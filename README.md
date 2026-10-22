EV Connect: A Responsive Web-Based EV Charging and Vehicle Facilities System 

1. Project Overview

EV Connect is a website that is responsive and designed to assist users in 
finding and accessing vehicle facilities, such as EV charging stations, petrol stations, 
car wash services, air pumps, and parking facilities.

The website is made to work on desktops, tablets, and mobile phones.

2. Main Features

- User registration and login
- Browse vehicle facilities
- Check the details and see if an EV charging station or vehicle facilities is available.
- Booking functionality for EV Charging 
- Responsive website layout
- Admin section for managing website information

3. Technologies Used

- HTML
- CSS
- PHP
- MySQL
- XAMPP
- phpMyAdmin

4. Installation and Setup

1. Install XAMPP.
2. Copy the project folder into `C:\xampp\htdocs\`.
3. Start Apache and MySQL by using the XAMPP Control Panel.
4. Open `http://localhost/phpmyadmin/`.
5. Use the database name that is listed in the `db.php` file to set up the database.
6. Choose the database and import the `ev.sql` file that has been provided.
7. Check the database connection settings in `db.php`.
8. Open `http://localhost/EVConnect_Project/` in your browser. 
If you need to, change the folder name in the URL.

5. Database Setup

The `ev.sql` file has the structure of the database and any example data that comes with the project. 
Import it into MySQL using phpMyAdmin.

6. Project Structure

- User website files: Main website pages
- `admin.css`: Admin pages
- `db.php`: Database connection
- `style.css`: Website styling
- `ev.sql`: Database setup script

7. Known Challenges and Solutions

The project required combining the website pages, linking PHP with MySQL, and making sure the layout looks good on various screen sizes.
These problems were fixed by combining the code, checking the database link, and making the CSS work better on different screen sizes.

8. Group Contributions

Yasarah Hamizah @Natasha binti Jaini (B20250054)

Yasarah is in charge of coming up with the first idea for the project 
and assigning tasks to each group member. She created the database 
and how the different parts of it connect, set up the admin login 
and dashboard, and built features for handling charging stations, 
charger availability, facilities, and bookings. She also made SQL 
reports and assisted with connecting the database and testing the
whole system.

Mek Jen Rui (B20250100) 

Mek Jen Rui’s design responsibilities is user registration, login, and 
logout processes, storing user data in a database, creating booking forms, 
checking for correct information, keeping track of past bookings, allowing 
users to cancel bookings, and making safe database searches.

Nurul Hafrina binti Hanapi (B20260336) 

Hafrina is in charge of making the HTML pages, creating the homepage and 
the navigation bar, and using CSS to style the website. She also created 
the pages for EV charging stations and vehicle facilities, and worked with 
other team members to make sure the website could link with the booking and database features.
In charge of creating the designs for the petrol station, car wash, air tire pump, 
and parking pages. She arranged the facility details in a clear way and kept the design
the same throughout, using the same colors, fonts, spacing, and card styles. She also looked 
at the pages and links to make sure they were working correctly.

9. Notes

Run the website through XAMPP using localhost. 
Opening PHP files straight from the file system won't run the PHP code.
