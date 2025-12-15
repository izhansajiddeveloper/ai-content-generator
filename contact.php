<?php
include 'includes/db.php';
include 'includes/header.php';

$message = '';
$messageType = '';

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $msg = mysqli_real_escape_string($conn, $_POST['message']);

    // Insert into contact_messages table with current timestamp
    $insert = mysqli_query($conn, "INSERT INTO contact_messages (name, email, subject, message, created_at) 
        VALUES ('$name', '$email', '$subject', '$msg', NOW())");

    if ($insert) {
        $message = "Thank you! Your message has been sent. We'll respond within 24 hours.";
        $messageType = 'success';
        
        // Clear form fields
        $_POST = array();
    } else {
        $message = "Something went wrong. Please try again.";
        $messageType = 'error';
    }
}
?>

<main class="min-h-screen bg-gradient-to-b from-gray-50 to-blue-50 py-12">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold mb-6">
                Get In Touch
            </span>
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                Contact <span class="text-blue-600">AI Learning Companion</span>
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Have questions, feedback, or need support? We're here to help you make the most of your learning journey.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Contact Information -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg p-8 h-full">
                    <h3 class="text-2xl font-bold text-gray-900 mb-8">Contact Information</h3>
                    
                    <div class="space-y-8">
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-1">Email Us</h4>
                                <p class="text-gray-600">support@ailearningcompanion.com</p>
                                <p class="text-gray-500 text-sm">We respond within 24 hours</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-1">Community</h4>
                                <p class="text-gray-600">Join our Discord server</p>
                                <p class="text-gray-500 text-sm">Connect with other learners</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-1">Documentation</h4>
                                <p class="text-gray-600">View tutorials & guides</p>
                                <p class="text-gray-500 text-sm">Learn how to use all features</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-12 pt-8 border-t border-gray-200">
                        <h4 class="font-bold text-gray-900 mb-4">Office Hours</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Monday - Friday</span>
                                <span class="font-medium">9:00 AM - 6:00 PM EST</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Saturday</span>
                                <span class="font-medium">10:00 AM - 4:00 PM EST</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Sunday</span>
                                <span class="font-medium">Closed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg p-8 h-full">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Send us a Message</h3>
                    <p class="text-gray-600 mb-8">Fill out the form below and we'll get back to you as soon as possible.</p>

                    <?php if ($message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <?php echo $message; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Full Name *</label>
                                <input type="text" 
                                       name="name" 
                                       required 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300"
                                       placeholder="John Doe">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Email Address *</label>
                                <input type="email" 
                                       name="email" 
                                       required 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300"
                                       placeholder="john@example.com">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Subject *</label>
                            <input type="text" 
                                   name="subject" 
                                   required 
                                   value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>"
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300"
                                   placeholder="How can we help you?">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Your Message *</label>
                            <textarea name="message" 
                                      rows="6" 
                                      required 
                                      class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300"
                                      placeholder="Please describe your question or feedback in detail..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <div class="flex items-start">
                            <input type="checkbox" 
                                   id="newsletter" 
                                   name="newsletter" 
                                   class="mt-1 mr-3 h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                            <label for="newsletter" class="text-gray-600 text-sm">
                                I'd like to receive updates about new features, learning tips, and educational resources.
                            </label>
                        </div>

                        <button type="submit" 
                                name="submit" 
                                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-4 px-6 rounded-lg transition duration-300 shadow-lg hover:shadow-xl">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Send Message
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-16">
            <h3 class="text-2xl font-bold text-center text-gray-900 mb-8">Frequently Asked Questions</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                    <h4 class="font-bold text-gray-900 mb-2">How quickly will I receive a response?</h4>
                    <p class="text-gray-600">We typically respond to all inquiries within 24 hours during business days.</p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                    <h4 class="font-bold text-gray-900 mb-2">Do you offer educational institution plans?</h4>
                    <p class="text-gray-600">Yes! We offer special plans for schools and universities. Contact us for pricing.</p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                    <h4 class="font-bold text-gray-900 mb-2">Can I request custom templates?</h4>
                    <p class="text-gray-600">Absolutely! We work with educators to create custom templates for specific needs.</p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                    <h4 class="font-bold text-gray-900 mb-2">Is my data secure and private?</h4>
                    <p class="text-gray-600">Yes, we take data privacy seriously. All your content is encrypted and secure.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
include 'includes/footer.php';
?>