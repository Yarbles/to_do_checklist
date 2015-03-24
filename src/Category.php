<?php

    class Category
    {
        private $name;
        private $id;

        function __construct($initial_name, $initial_id = null)
        {
            $this->name = $initial_name;
            $this->id = $initial_id;
        }

        function getName()
        {
            return $this->name;
        }

        function setName($new_name)
        {
            $this->name = (string) $new_name;
        }

        function getId()
        {
            return $this->id;
        }

        function setId($new_id)
        {
            $this->id = (int) $new_id;
        }

        function save()
        {
            $statement = $GLOBALS['DB']->query("INSERT INTO categories (name) VALUES ('{$this->getName()}') RETURNING id;");
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            $this->setId($result['id']);
        }

        static function getAll()
        {
            $returned_categories = $GLOBALS['DB']->query("SELECT * FROM categories;");
            $categories = array();
            foreach($returned_categories as $category) {
                $name = $category['name'];
                $id = $category['id'];
                $new_category = new Category($name, $id);
                array_push($categories, $new_category);
            }
            return $categories;
        }

        static function deleteAll()
        {
            $GLOBALS['DB']->exec("DELETE FROM categories *;");
        }

        static function find($search_id)
        {
            $found_category = null;
            $categories = Category::getAll();
            foreach($categories as $category) {
                $category_id = $category->getId();
                if ($category_id == $search_id) {
                  $found_category = $category;
                }
            }
            return $found_category;
        }

        function update($new_name)
        {
            $GLOBALS['DB']->exec("UPDATE categories SET name = '{$new_name}' WHERE id = {$this->getId()};");
            $this->setName($new_name);
        }

        function delete()
        {
            $GLOBALS['DB']->exec("DELETE FROM categories WHERE id = {$this->getId()};");
            $GLOBALS['DB']->exec("DELETE FROM categories_tasks WHERE category_id = {$this->getId()};");
        }

        // function getTasks()
        // {
        //     //get all task ids
        //     //from the join table where task ids are stored with category ids
        //     //return the task ids which correspond to category ids equal to the current category's id.
        //     $query = $GLOBALS['DB']->query("SELECT task_id FROM categories_tasks WHERE category_id = {$this->getId()};");
        //     $task_ids = $query->fetchAll(PDO::FETCH_ASSOC); //format task ids as an associative array.
        //
        //     $tasks = array(); //create an empty array to return at the end filled with all tasks assigned to current category's id.
        //     foreach($task_ids as $id) { //go through each task_id and store it in $id
        //         $task_id = $id['task_id'];              //pull out its value with the key 'task_id' and store it in variable $task_id
        //
        //         //get all tasks matching the current task id out of the tasks table (including their description).
        //         $result = $GLOBALS['DB']->query("SELECT * FROM tasks WHERE id = {$task_id};");
        //         //format as associative array and store in $returned_task.
        //         $returned_task = $result->fetchAll(PDO::FETCH_ASSOC);
        //
        //         //get the task's description and id by looking in the first item in $returned_task under the column names as keys.
        //         $description = $returned_task[0]['description'];
        //         $id = $returned_task[0]['id'];
        //
        //         //instantiate a task object using the data from the current task table row, just like in getAll.
        //         $new_task = new Task($description, $id);
        //         //push it into the $tasks array to be output after loop.
        //         array_push($tasks, $new_task);
        //     }
        //     return $tasks;
        // }

        function getTasks()
        {
            $query = $GLOBALS['DB']->query("SELECT tasks.* FROM
                categories JOIN categories_tasks ON (categories.id = categories_tasks.category_id)
                            JOIN tasks ON (categories_tasks.task_id = tasks.id)
                WHERE categories.id= {$this->getId()};");
                $tasks_temp = $query->fetchAll(PDO::FETCH_ASSOC);
                var_dump($tasks_temp);
                $tasks = array();
                foreach($tasks_temp as $task) {
                    $description = $task[0]['description'];
                    $id = $task[0]['id'];
                    $new_task = new Task ($description, $id);
                    $new_task1= $new_task->getDescription();
                    array_push($tasks, $new_task);
                }

            return $tasks;

        }


        function addTask($task)
        {
            //save the id of the current category with the id of the input $task into a row in the join table called categories_tasks.
            $GLOBALS['DB']->exec("INSERT INTO categories_tasks (category_id, task_id) VALUES ({$this->getId()}, {$task->getId()});");
        }
    }


?>
