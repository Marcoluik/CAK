const express = require('express');
const nodemailer = require('nodemailer');
const cors = require('cors');
require('dotenv').config();

const app = express();
app.use(cors());
app.use(express.json());

// Create a transporter using SMTP
const transporter = nodemailer.createTransport({
    host: process.env.SMTP_HOST,
    port: process.env.SMTP_PORT,
    secure: true,
    auth: {
        user: process.env.SMTP_USER,
        pass: process.env.SMTP_PASS
    }
});

// Contact form endpoint
app.post('/api/contact', async (req, res) => {
    try {
        const { name, email, phone, zipcode, address, house_type, message } = req.body;

        // Create email content
        const mailOptions = {
            from: process.env.SMTP_USER,
            to: 'kontakt@cakisolering.dk',
            subject: 'Ny kontaktformular forespørgsel',
            html: `
                <h2>Ny forespørgsel fra kontaktformularen</h2>
                <p><strong>Navn:</strong> ${name}</p>
                <p><strong>Email:</strong> ${email}</p>
                <p><strong>Telefon:</strong> ${phone}</p>
                <p><strong>Postnummer:</strong> ${zipcode}</p>
                <p><strong>Adresse:</strong> ${address}</p>
                <p><strong>Boligtype:</strong> ${house_type}</p>
                ${message ? `<p><strong>Besked:</strong> ${message}</p>` : ''}
            `
        };

        // Send email
        await transporter.sendMail(mailOptions);

        res.status(200).json({ message: 'Email sent successfully' });
    } catch (error) {
        console.error('Error sending email:', error);
        res.status(500).json({ error: 'Failed to send email' });
    }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
}); 