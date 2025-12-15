# AI Content Generator

![AI Content Generator](screenshots/dashboard.png)

**AI Content Generator** is a web-based application that allows users to generate high-quality educational content instantly using AI. The system can create summaries, key points, quizzes, examples, full assignments, and explanations for both kids and experts. This project is built with **PHP**, **MySQL**, **HTML**, **CSS**, and integrates **Google Gemini AI API** for content generation.

---
![imagealt](https://github.com/izhansajiddeveloper/ai-content-generator/blob/89f22d2b5dbf59c701747e262c4745bbf55aa1a0/web1.png)

## 🔹 Features

- **User Authentication** – Secure login system for users.
- **Content Generation** – Generate AI-powered content based on your input text.
- **Multiple Output Types**:
  - 📋 **Summary** – Concise overview
  - 🔑 **Key Points** – Bullet points of main ideas
  - ❓ **Quiz** – 5 multiple-choice questions with answers
  - 💡 **Examples** – Practical examples with explanations
  - 📝 **Full Assignment** – Structured academic assignment
  - 🧒 **Explanation for Kids** – Simplified explanation
  - 🎓 **Explanation for Experts** – Detailed analysis
- **Save as PDF** – Download the generated content as a styled PDF.
- **Copy to Clipboard** – Quickly copy content for use elsewhere.
- **Category & Difficulty Selection** – Customize generated content based on your preferences.

![image alt](https://github.com/izhansajiddeveloper/ai-content-generator/blob/68cbf0e57f3fb775b834f52e67da5494d160a698/WEB2.png)

---

## 🔹 Technologies Used

- **Backend**: PHP, MySQL  
- **Frontend**: HTML, CSS, TailwindCSS  
- **AI Integration**: Google Gemini AI API  
- **PDF Generation**: TCPDF Library  

![image alt](https://github.com/izhansajiddeveloper/ai-content-generator/blob/7319e68667c63ba9e92f5653ade356062e71d10e/WEB3.png)

Installation & Setup

Clone the repository:

git clone https://github.com/izhansajiddeveloper/ai-content-generator.git
cd ai-content-generator


Install dependencies (if any):

Make sure PHP and MySQL are installed.

Ensure cURL and openssl are enabled in PHP.

Create a database in MySQL (e.g., ai_content_db) and import the database.sql file if provided.

Configure the database:

Open includes/db.php.

Set your MySQL username, password, and database name.

Set your Google Gemini API key:

Open includes/openai.php.

Replace the placeholder API key with your own.

Important: Do not commit API keys to public repositories. Use environment variables or .env files.

Run the project:

Start your local server (XAMPP, WAMP, or similar).

Visit http://localhost/ai-content-generator in your browser.

🔹 Usage

Register or Login.

Paste your content in the generator text box.

Select category, difficulty, and output type.

Click Generate to get AI-powered content.

Copy, download as PDF, or save for later.
