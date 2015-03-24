DATABASE COMMANDS:

CREATE DATABASE to_do;

\c to_do

CREATE TABLE tasks (id serial PRIMARY KEY, description varchar);

CREATE TABLE categories (id serial PRIMARY KEY, name varchar);

CREATE TABLE categories_tasks (id serial PRIMARY KEY, category_id int, task_id int);

CREATE DATABASE to_do_test WITH TEMPLATE to_do;

TO IMPORT:

In psql: 
CREATE DATABASE to_do;
\c to_do;

In new terminal window:
Change directory to cloned project directory.

\i to_do.sql

Back In psql:
CREATE DATABASE to_do_test;
\c to_do_test;

Back in terminal window:
\i to_do_test.sql