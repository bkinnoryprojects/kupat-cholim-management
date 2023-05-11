# kupat-cholim-management
First, make sure you have installed the SQL database management system,  MySQL on your computer. You will also need to have PHP installed on your computer, along with a web server such as Apache or Nginx. Nginx.

You can install them all at once by installing wamp software.

https://wampserver.aviatechno.net/
 After downloading, run the softwarewamp.
 
Next, create a database in your SQL management system Use the attached SQL file.

Once you have created your database, you will need to set up a database user and password with appropriate permissions to access and modify the database.

In your PHP code, you will need to use the appropriate PHP functions to connect to your SQL database. 

In the PHP code, the username and password and the DB name must be entered in the first command in the file index.php :

db('localhost', 'usernam', 'db_password', 'db_database', 'charset = 'utf8mb4');

For the client side, you can use the Postman software. 
https://www.postman.com/downloads/



