# COMP3010 Project

## Overview
This project is a PHP-based application that includes various functionalities such as managing students, generating report cards, and defining rubrics. It also integrates the TCPDF library for generating PDF documents.

## Features
- Add and manage students
- Generate and edit report cards
- Define rubrics for assessments
- User authentication (login/logout)
- Dashboard for quick access to features

## Dependencies
- **PHP**: Ensure PHP is installed on your system.
- **TCPDF**: A PHP library for generating PDF documents. The library is included in the `tcpdf/` directory.
- **MySQL**: Used for database management. The schema is defined in `schema.sql`.

## Setup Instructions
1. Clone or download the project to your local machine.
2. Set up a web server (e.g., XAMPP) and place the project in the `htdocs` directory.
3. Import the database schema:
   - Open a MySQL client (e.g., phpMyAdmin).
   - Import the `schema.sql` file to create the necessary database structure.
4. Configure the database connection:
   - Edit the `config/db_config.php` file with your database credentials.
5. Start the web server and access the application via `http://localhost/COMP3010`.

## Usage
- **Login**: Use the `login.php` page to authenticate.
- **Dashboard**: Access the main features from the `dashboard.php`.
- **Manage Students**: Add or bulk add students using `add_student.php` and `bulk_add_students.php`.
- **Generate Reports**: Use `generate_report_card.php` to create PDF reports.
- **Edit Reports**: Modify existing reports via `edit_report_card.php`.

## File Structure
- `add_student.php`: Add individual students.
- `bulk_add_students.php`: Add multiple students at once.
- `dashboard.php`: Main dashboard for navigation.
- `db.php`: Database connection helper.
- `define_rubric.php`: Define rubrics for assessments.
- `edit_report_card.php`: Edit existing report cards.
- `generate_report_card.php`: Generate PDF report cards.
- `login.php`: User login page.
- `logout.php`: User logout page.
- `schema.sql`: Database schema.
- `students.php`: Manage student records.
- `student_report.php`: View individual student reports.
- `tcpdf/`: TCPDF library for PDF generation.

## License
This project is licensed under the GNU General Public License v3.0. See the `LICENSE.TXT` file in the `tcpdf/` directory for details.

## Acknowledgments
- **TCPDF**: For providing the PDF generation library.
- **XAMPP**: For the local development environment.

## Contact
For any issues or inquiries, please contact the project maintainer.