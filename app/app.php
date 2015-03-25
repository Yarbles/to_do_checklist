<?php
    require_once __DIR__."/../vendor/autoload.php";
    require_once __DIR__."/../src/Task.php";
    require_once __DIR__."/../src/Category.php";
    use Symfony\Component\Debug\Debug;
    Debug::enable();

    $app = new Silex\Application();
    $app['debug'] = true;

    $DB = new PDO('pgsql:host=localhost;dbname=to_do');

    $app->register(new Silex\Provider\TwigServiceProvider(), array(
        'twig.path' => __DIR__.'/../views'
    ));

    use Symfony\Component\HttpFoundation\Request;
    Request::enableHttpMethodParameterOverride();

    $app->get("/", function() use ($app) {
        return $app['twig']->render('index.html.twig');
    });

    //get
    //READ (all) tasks
    $app->get("/tasks", function() use ($app) {


        return $app['twig']->render('tasks.html.twig', array('tasks' => Task::getAll()));
    });

    //READ (all) categories
    $app->get("/categories", function() use ($app) {
        return $app['twig']->render('categories.html.twig', array('categories' => Category::getAll()));
    });

    //READ (singular) task
    $app->get("/tasks/{id}", function($id) use ($app) {
        $task = Task::find($id);
        $query = $GLOBALS['DB']->query("SELECT completion FROM tasks WHERE id = $id ;");
        $temp = $query->fetchAll(PDO::FETCH_ASSOC);
        $completion =$temp[0]['completion'];

        return $app['twig']->render('task.html.twig', array('task' => $task, 'id'=> $task->getId(), 'completion'=>$completion, 'categories' => $task->getCategories(), 'all_categories' => Category::getAll()));
    });

    //READ (singular) category
    $app->get("/categories/{id}", function($id) use ($app) {
        $category = Category::find($id);
        return $app['twig']->render('category.html.twig', array('category' => $category, 'tasks' => $category->getTasks(), 'all_tasks' => Task::getAll()));
    });

    //READ edit forms

    //these routes display an edit form for each class. Since the request is only saying "GET me the edit form and show it to me" these routes can use the GET method.
    //add a link to this route to edit the current task or category from task.html.twig and category.html.twig
    //the edit forms should submit to tasks/{id} and categories/{id} with a patch method.
    $app->get("/tasks/{id}/edit", function($id) use ($app) {
        $task = Task::find($id);
        $query = $GLOBALS['DB']->query("SELECT completion FROM tasks WHERE id = $id ;");
        $temp = $query->fetchAll(PDO::FETCH_ASSOC);
        $completion =$temp[0]['completion'];
        return $app['twig']->render('task_edit.html.twig', array('task' => $task , 'completion'=>$completion, 'id'=>$task->getId()));
    });


    $app->get("/categories/{id}/edit", function($id) use ($app) {
        $category = Category::find($id);
        return $app['twig']->render('category_edit.html.twig', array('category' => $category));
    });

    //post
    //CREATE task
    //to get here, send form from tasks.html.twig. shown with get /tasks.
    $app->post("/tasks", function() use ($app) {
        $description = $_POST['description'];
        $task = new Task($description);
        $task->save();
        return $app['twig']->render('tasks.html.twig', array('tasks' => Task::getAll()));
    });

    //CREATE category
    $app->post("/categories", function() use ($app) {
        $name = $_POST['name'];
        $category = new Category($name);
        $category->save();
        return $app['twig']->render('categories.html.twig', array('categories' => Category::getAll()));
    });

    //CREATE add tasks to category. send here from form in category.html.twig
    $app->post("/add_tasks", function() use ($app) {
        $category = Category::find($_POST['category_id']);
        $task = Task::find($_POST['task_id']);
        $category->addTask($task);
        return $app['twig']->render('category.html.twig', array('category' => $category, 'categories' => Category::getAll(), 'tasks' => $category->getTasks(), 'all_tasks' => Task::getAll()));
    });

    //CREATE add categories to task. send here from form in task.html.twig
    $app->post("/add_categories", function() use ($app) {
        $category = Category::find($_POST['category_id']);
        $task = Task::find($_POST['task_id']);
        $task->addCategory($category);
        return $app['twig']->render('task.html.twig', array('task' => $task, 'tasks' => Task::getAll(), 'categories' => $task->getCategories(), 'all_categories' => Category::getAll()));
    });

    //delete
    //DELETE (all) tasks, then route back to root.
    //present form on the tasks.html.twig page
    $app->delete("/delete_tasks", function() use ($app){
        Task::deleteAll();
        return $app['twig']->render('index.html.twig');
    });

    //DELETE (all) categories, then route back to root.
    //present form on the categories.html.twig page
    $app->delete("/delete_categories", function() use ($app){
        Category::deleteAll();
        return $app['twig']->render('index.html.twig');
    });

    //delete singular / current category from link on the edit category page.
    //after deleting the category, render the page to view all categories.
    $app->delete("/categories/{id}", function($id) use ($app){
        //get the current category using the id sent in the URL defined in the delete form's action
        $category = Category::find($id);
        //delete it
        $category->delete();
        return $app['twig']->render('categories.html.twig', array('categories' => Category::getAll()));
    });

    //delete singular / current task from link on the edit task page.
    //after deleting the task, render the page to view all tasks.
    $app->delete("/tasks/{id}", function($id) use ($app){
        $task = Task::find($id);
        $task->delete();
        return $app['twig']->render('tasks.html.twig', array('tasks' => Task::getAll()));
    });

    //patch
    //these two patch routes are called from the edit form for each object

    //AFTER EDITING A TASK
    //we need to pass in:
    //the current task that has just been edited under 'task'
    //as well as all categories associated with the current task so that they can be displayed under 'categories'
    //as well as all categories that have been created under 'all_categories' so that they can be included in the dropdown menu and new categories from the list can be assigned to the current task.
    $app->patch("/tasks/{id}", function($id) use ($app) {
        $description = $_POST['description'];
        $task = Task::find($id);
        $task->update($description);

        $query = $GLOBALS['DB']->query("SELECT completion FROM tasks WHERE id = $id ;");
        $temp = $query->fetchAll(PDO::FETCH_ASSOC);
        $completion =$temp[0]['completion'];
        return $app['twig']->render('task.html.twig', array('task' => $task, 'completion'=>$completion, 'categories' => $task->getCategories(), 'id' => $task->getId(), 'all_categories' => Category::getAll()));
    });

    $app->post("/tasks/{id}", function($id) use ($app) {
        $completion = $_POST['completion'];

         $task = Task::find($id);

        if ($completion) {
                $completion_query = $GLOBALS['DB']->query("UPDATE tasks SET completion = 'completed' WHERE id = $id;");
        }

        $query = $GLOBALS['DB']->query("SELECT completion FROM tasks WHERE id = $id ;");
        $temp = $query->fetchAll(PDO::FETCH_ASSOC);
        $completion =$temp[0]['completion'];
        return $app['twig']->render('task.html.twig', array('task' => $task, 'completion'=>$completion, 'id'=>$task->getId(), 'categories' => $task->getCategories(), 'all_categories' => Category::getAll()));
    });


    //AFTER EDITING A CATEGPRU
    //we need to pass in:
    //the current category that has just been edited under 'category'
    //as well as all tasks associated with the current category so that they can be displayed under 'tasks'
    //as well as all tasks that have been created under 'all_tasks' so that they can be included in the dropdown menu and new tasks from the list can be assigned to the current category.
    $app->patch("/categories/{id}", function($id) use ($app) {
        $name = $_POST['name'];
        $category = Category::find($id);
        $category->update($name);
        return $app['twig']->render('category.html.twig', array('category' => $category, 'tasks' => $category->getTasks(), 'all_tasks' => Task::getAll()));
    });


    return $app;

?>
