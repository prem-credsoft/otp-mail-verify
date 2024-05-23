### Mail OTP Verification

The OTP (One-Time Password) verification system implemented in `index.php` uses PHPMailer for sending emails and dotenv for environment variable management. Here's a brief overview of how the system works:

1. **Environment Setup**: 
   - Use Composer to install PHPMailer and PHP dotenv.
   - Create a `.env.local` file to securely store sensitive information such as email credentials.

2. **Database Connection**:
   - Establish a connection to a MySQL database to store and manage OTPs associated with user emails.

3. **Sending OTP**:
   - When a user submits their email, an OTP is generated and stored in the database along with the current timestamp.
   - An email is sent to the user with the OTP using PHPMailer, configured to use Gmail's SMTP server.

4. **Verifying OTP**:
   - The user submits the received OTP through the web interface.
   - The OTP is verified against the stored value in the database. It checks if the OTP is correct and has not expired (valid for 10 minutes).

5. **Frontend**:
   - HTML forms are provided for the user to input their email and OTP.
   - jQuery is used to handle form submissions and communicate with the server via AJAX.

### Setup Instructions

1. **Install Dependencies**:
   ```bash
      composer require phpmailer/phpmailer
      composer require vlucas/phpdotenv
   ```

2. **Create .env.local File**:
   - Place it in the root directory of your project.
   - Add the following content, replacing placeholders with your actual email credentials:
     
   ```bash
     MAIL_USERNAME=your_email@gmail.com
     MAIL_PASSWORD=your_password
     MAIL_FROM_EMAIL=your_email@gmail.com
     MAIL_FROM_NAME=Your Name
   ```

This setup ensures that the OTP verification process is secure and efficient, leveraging modern PHP libraries and practices.
