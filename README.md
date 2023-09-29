Todo List API
This project is an API for managing your to-do list. Each task in the to-do list has the following properties:

status (todo, done)
priority (1...5)
title: Task title
description: Task description
createdAt: Creation date
completedAt: Completion date
Tasks can have subtasks, and there is no limit to the nesting level of subtasks.

The API provides the following functionalities:

Get a list of your tasks based on filtering criteria
Create a new task
Edit an existing task
Delete a task
Mark a task as completed
When retrieving a list of tasks, users can:

Filter tasks by status (todo, done)
Filter tasks by priority (from and to values)
Perform a full-text search on task titles
Sort tasks by creation time, completion time, or priority
Users are not allowed to:

Modify or delete tasks that do not belong to them
Delete completed tasks
Mark a task as completed if it has uncompleted subtasks

Minimum Requirements
PHP 8.0
Framework
Laravel / Symfony
Project Name
Project Name: todo_list_api
Project Goal: See project description for details
Getting Started
Follow these steps to set up the project locally:

1. Clone the repository:  git clone https://github.com/AnKrash/todo_list_api.git

2. Install dependencies: cd todo_list_api
composer install

3. Configure the environment:

Create a .env file based on .env.example and configure the database and other settings:
cp .env.example .env

4. Generate an application key: php artisan key:generate

5. Run database migrations: php artisan migrate

6. Seed the database:  php artisan db:seed --class=UserSeeder   php artisan db:seed --class=TaskSeeder

7. Start the local server: php artisan serve

8. Access the project in your browser at http://localhost:8000.

Now you are ready to work on the project locally!

Note: Ensure that you have the necessary PHP version and Composer installed on your system before proceeding.

Note: The Postman collection for testing the application is stored in the folder:todo_list-api/postman_collection

Note: Before testing Update and Delete in Postman you should get a Bearer Token for authorization (use Login from postman_collection) and install it in Update and Delete.
