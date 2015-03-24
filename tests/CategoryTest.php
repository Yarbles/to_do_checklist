<?php

    /**
    * @backupGlobals disabled
    * @backupStaticAttributes disabled
    */

    require_once "src/Task.php";
    require_once "src/Category.php";

    $DB = new PDO('pgsql:host=localhost;dbname=to_do_test');


    class CategoryTest extends PHPUnit_Framework_TestCase
    {
        protected function tearDown()
        {
            Category::deleteAll();
            Task::deleteAll();
        }

        //Initialize a Category with a name and be able to get it back out of the object using getName().
        function testGetName()
        {
            //Arrange
            $name = "Kitchen chores";
            $test_category = new Category($name);
            //No need to save here because we are communicating with the object only and not the database.

            //Act
            $result = $test_category->getName();

            //Assert
            $this->assertEquals($name, $result);

        }

        function testSetName()
        { //can I change the name in the object with setName() after initializing it?
            //Arrange
            $name = "Kitchen chores";
            $test_category = new Category($name);
            //No need to save here because we are communicating with the object only and not the database.

            //Act
            $test_category->setName("Home chores");
            $result = $test_category->getName();

            //Assert
            $this->assertEquals("Home chores", $result);
        }

        //Next, let's add the Id property to our Category class. Like any other property it needs a getter and setter.
        //Create a Category with the id in the constructor and be able to get the id back out.
        function testGetId()
        {
            //Arrange
            $id = 1;
            $name = "Kitchen chores";
            $test_category = new Category($name, $id);

            //Act
            $result = $test_category->getId();

            //Assert
            $this->assertEquals(1, $result);
        }

        //Create a Category with the id in the constructor and be able to change its value, and then get the new id out.
        function testSetId()
        {
            //Arrange
            $id = 1;
            $name = "Kitchen chores";
            $test_category = new Category($name, $id);

            //Act
            $test_category->setId(2);

            //Assert
            $result = $test_category->getId();
            $this->assertEquals(2, $result);
        }


        //CREATE - save method stores all object data in categories table.
        function testSave()
        {
            //Arrange
            $name = "Work stuff";
            $id = 1;
            $test_category = new Category($name, $id);
            $test_category->save();

            //Act
            $result = Category::getAll();

            //Assert
            $this->assertEquals($test_category, $result[0]);
        }

        //This test makes sure that after saving not only are the id's equal, they are not null.
        function testSaveSetsId()
        {
            //Arrange
            $name = "Work stuff";
            $id = 1;
            $test_category = new Category($name, $id);

            //Act
            //save it. Id should be assigned in database, then stored in object.
            $test_category->save();

            //Assert
            //That id in the object should be numeric (not null)
            $this->assertEquals(true, is_numeric($test_category->getId()));
        }

        //READ - All categories
        //This method should return an array of all Category objects from the categories table.
        //Since it isn't specifically for only one Category, it is for all, it should be a static method.
        function testGetAll()
        {
            //Arrange
            $name = "Work stuff";
            $id = 1;
            $test_category = new Category($name, $id);
            $test_category->save();

            $name2 = "Home stuff";
            $id2 = 2;
            $test_category2 = new Category($name2, $id2);
            $test_category2->save();

            //Act
            $result = Category::getAll();

            //Assert
            $this->assertEquals([$test_category, $test_category2], $result);
        }

        //DELETE - All categories
        //Since this also deals with more than one Category it should be a static method.
        function testDeleteAll()
        {
            //Arrange
            //We need some categories saved into the database so that we can make sure our deleteAll method removes them all.
            $name = "Wash the dog";
            $id = 1;
            $test_category = new Category($name, $id);
            $test_category->save();

            $name2 = "Water the lawn";
            $id2 = 2;
            $test_category2 = new Category($name2, $id2);
            $test_category2->save();

            //Act
            //Delete categories.
            Category::deleteAll();

            //Assert
            //Now when we call getAll, we should get an empty array because we deleted all categories.
            $result = Category::getAll();
            $this->assertEquals([], $result);
        }

        //Test for find category method.
        //Static method to select a category using its id number as input.
        function testFind()
        {
            //Arrange
            //Create and save 2 categories.
            $name = "Wash the dog";
            $id = 1;
            $test_category = new Category($name, $id);
            $test_category->save();

            $name2 = "Home stuff";
            $id2 = 2;
            $test_category2 = new Category($name2, $id2);
            $test_category2->save();

            //Act
            //search using the id of the first category.
            $result = Category::find($test_category->getId());

            //Assert
            //if the search function works then result should be the first category.
            //ids are unique so this method will always return a single instance.
            //no need for returning an array like in getAll.
            $this->assertEquals($test_category, $result);
        }

        function testUpdate()
        {
            //Arrange
            $name = "Work stuff";
            $id = 1;
            $test_category = new Category($name, $id);
            $test_category->save();

            $new_name = "Home stuff";

            //Act
            $test_category->update($new_name);

            //Assert
            $this->assertEquals("Home stuff", $test_category->getName());
        }

        function testDeleteCategory()
        {
            //Arrange
            $name = "Work stuff";
            $id = 1;
            $test_category = new Category($name, $id);
            $test_category->save();

            $name2 = "Home stuff";
            $id2 = 2;
            $test_category2 = new Category($name2, $id2);
            $test_category2->save();


            //Act
            $test_category->delete();

            //Assert
            $this->assertEquals([$test_category2], Category::getAll());
        }

        //This addTask() method will assign a Task object to the current Category object by saving their ids in the join table.
        // function testAddTask()
        // {
        //     //Arrange
        //     //We need a category and a task saved
        //     $name = "Work stuff";
        //     $id = 1;
        //     $test_category = new Category($name, $id);
        //     $test_category->save();
        //
        //     $description = "File reports";
        //     $id2 = 2;
        //     $test_task = new Task($description, $id2);
        //     $test_task->save();
        //
        //     //Act
        //     //call add task method on test category to assign test task to it.
        //     //add task method takes an entire Task object as input and assigns it to the category the method has been called on.
        //     $test_category->addTask($test_task);
        //
        //     //Assert
        //     //now if we get the tasks associated with the category we should get the one we assigned back
        //     //our test task should be returned in an array because there can be more than one task associated with a category.
        //     //we still need to write the getTasks() method to get the tasks associated with the category,
        //     //but now we know how we want to use it.
        //     $this->assertEquals($test_category->getTasks(), [$test_task]);
        // }

        //Now we write a test for the getTasks method since we need it to be able to test the Add Task method.
        // function testGetTasks()
        // {
        //     //Arrange
        //     //start with a category
        //     $name = "Home stuff";
        //     $id = 1;
        //     $test_category = new Category($name, $id);
        //     $test_category->save();
        //
        //     //create 2 tasks to assign to it.
        //     $description = "Wash the dog";
        //     $id2 = 2;
        //     $test_task = new Task($description, $id2);
        //     $test_task->save();
        //     // $test_task_array= array();
        //     // $test_task_array = $test_task;
        //     // var_dump($test_task_array);
        //
        //     $description2 = "Take out the trash";
        //     $id3 = 3;
        //     $test_task2 = new Task($description2, $id3);
        //     $test_task2->save();
        //
        //     //Act
        //     //add both tasks to the category
        //     $test_category->addTask($test_task);
        //     $test_category->addTask($test_task2);
        //     $test_result=$test_category->getTasks();
        //     //$test_result1=$test_result[0];
        //     var_dump($test_result);
        //
        //
        //
        //     //Assert
        //     //we should get both of them back when we call getTasks on the test category.
        //     $this->assertEquals($test_result, [$test_task, $test_task2]);
        // }

        //When we call delete on a category it should delete all mention of that category from both the categories table and the join table.
        //if we delete the category 'work stuff' and then later ask the 'file reports' task which categories it belongs to,
        //we wouldn't want it to tell us it is assigned to one that doesn't exist anymore.
        //we don't want to delete the task, just any mention of the category it was associated with in the join table.
        // function testDelete()
        // {
        //     //Arrange
        //     $name = "Work stuff";
        //     $id = 1;
        //     $test_category = new Category($name, $id);
        //     $test_category->save();
        //
        //     $description = "File reports";
        //     $id2 = 2;
        //     $test_task = new Task($description, $id2);
        //     $test_task->save();
        //
        //     //Act
        //     $test_category->addTask($test_task);
        //     $test_category->delete();
        //
        //     //Assert
        //     $this->assertEquals([], $test_task->getCategories());
        // }

    }
?>
