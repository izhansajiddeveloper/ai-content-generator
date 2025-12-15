<?php
// Include database connection
include 'includes/db.php';

// Include header
include 'includes/header.php';
?>

<!-- Main Content -->
<main class="min-h-screen bg-gradient-to-b from-gray-50 to-blue-50">
    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="text-center max-w-4xl mx-auto mb-16">
            <span class="inline-block px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold mb-6">AI-Powered Learning Platform</span>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                Transform Your Learning with
                <span class="text-blue-600">AI Companion</span>
            </h1>
            <p class="text-xl text-gray-600 mb-10 max-w-3xl mx-auto leading-relaxed">
                Generate summaries, key points, quizzes, examples, and assignments instantly.
                Save outputs, manage history, choose difficulty levels, and download as PDF.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="generator.php"
                    class="bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-700 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    Start Generating Now
                </a>
                <a href="#features"
                    class="bg-white text-blue-600 border-2 border-blue-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-50 transition duration-300">
                    Explore Features
                </a>
            </div>
        </div>

        <!-- Dashboard Preview -->
        <div class="bg-white rounded-2xl shadow-2xl p-2 mb-20 border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 p-4 rounded-lg flex justify-start space-x-2">
                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                <div class="w-3 h-3 rounded-full bg-green-500"></div>
            </div>
            <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">AI Learning Dashboard</h3>
                    <div class="space-y-4">
                        <div class="flex items-center p-4 bg-blue-50 rounded-lg">
                            <div class="w-10 h-10 flex items-center justify-center bg-blue-100 rounded-full mr-4">
                                <span class="text-blue-600 font-bold">📚</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Smart Summaries</h4>
                                <p class="text-sm text-gray-600">Complex topics simplified</p>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-green-50 rounded-lg">
                            <div class="w-10 h-10 flex items-center justify-center bg-green-100 rounded-full mr-4">
                                <span class="text-green-600 font-bold">🧠</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Interactive Quizzes</h4>
                                <p class="text-sm text-gray-600">Test your knowledge</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Quick Generation</h3>
                    <div class="space-y-4">
                        <div class="flex items-center p-4 bg-purple-50 rounded-lg">
                            <div class="w-10 h-10 flex items-center justify-center bg-purple-100 rounded-full mr-4">
                                <span class="text-purple-600 font-bold">📝</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Assignments</h4>
                                <p class="text-sm text-gray-600">Practice problems & solutions</p>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-orange-50 rounded-lg">
                            <div class="w-10 h-10 flex items-center justify-center bg-orange-100 rounded-full mr-4">
                                <span class="text-orange-600 font-bold">📊</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Key Points</h4>
                                <p class="text-sm text-gray-600">Essential information extracted</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <section id="features" class="mb-20">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-12">Powerful Learning Features</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition duration-300">
                    <div class="w-14 h-14 flex items-center justify-center bg-blue-100 rounded-xl mb-6">
                        <span class="text-2xl">📋</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Smart Templates</h3>
                    <p class="text-gray-600 mb-6">Generate content using intelligent templates for summaries, quizzes, and assignments tailored to your needs.</p>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center"><span class="text-blue-500 mr-2">✓</span> Multiple output formats</li>
                        <li class="flex items-center"><span class="text-blue-500 mr-2">✓</span> Customizable templates</li>
                        <li class="flex items-center"><span class="text-blue-500 mr-2">✓</span> Topic-specific generation</li>
                    </ul>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition duration-300">
                    <div class="w-14 h-14 flex items-center justify-center bg-green-100 rounded-xl mb-6">
                        <span class="text-2xl">📁</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Save & Manage</h3>
                    <p class="text-gray-600 mb-6">Store your generated content, organize by category, and access your full history anytime.</p>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> History tracking</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Category organization</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Quick retrieval</li>
                    </ul>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition duration-300">
                    <div class="w-14 h-14 flex items-center justify-center bg-purple-100 rounded-xl mb-6">
                        <span class="text-2xl">⚙️</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Customization</h3>
                    <p class="text-gray-600 mb-6">Adjust difficulty levels, select categories, and personalize outputs to match your learning goals.</p>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center"><span class="text-purple-500 mr-2">✓</span> Difficulty settings</li>
                        <li class="flex items-center"><span class="text-purple-500 mr-2">✓</span> Category selection</li>
                        <li class="flex items-center"><span class="text-purple-500 mr-2">✓</span> Personalized outputs</li>
                    </ul>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition duration-300">
                    <div class="w-14 h-14 flex items-center justify-center bg-orange-100 rounded-xl mb-6">
                        <span class="text-2xl">📄</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">PDF Export</h3>
                    <p class="text-gray-600 mb-6">Download your generated content as professional PDF documents for offline study and sharing.</p>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center"><span class="text-orange-500 mr-2">✓</span> Clean formatting</li>
                        <li class="flex items-center"><span class="text-orange-500 mr-2">✓</span> Printable layouts</li>
                        <li class="flex items-center"><span class="text-orange-500 mr-2">✓</span> Shareable documents</li>
                    </ul>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition duration-300">
                    <div class="w-14 h-14 flex items-center justify-center bg-red-100 rounded-xl mb-6">
                        <span class="text-2xl">🔍</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Examples & Explanations</h3>
                    <p class="text-gray-600 mb-6">Get practical examples and clear explanations to deepen your understanding of any topic.</p>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center"><span class="text-red-500 mr-2">✓</span> Real-world examples</li>
                        <li class="flex items-center"><span class="text-red-500 mr-2">✓</span> Step-by-step explanations</li>
                        <li class="flex items-center"><span class="text-red-500 mr-2">✓</span> Conceptual clarity</li>
                    </ul>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition duration-300">
                    <div class="w-14 h-14 flex items-center justify-center bg-indigo-100 rounded-xl mb-6">
                        <span class="text-2xl">📱</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Access Anywhere</h3>
                    <p class="text-gray-600 mb-6">Responsive design works on all devices - desktop, tablet, and mobile for learning on the go.</p>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center"><span class="text-indigo-500 mr-2">✓</span> Mobile-friendly</li>
                        <li class="flex items-center"><span class="text-indigo-500 mr-2">✓</span> Cross-device sync</li>
                        <li class="flex items-center"><span class="text-indigo-500 mr-2">✓</span> Offline access to PDFs</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-8 md:p-12 text-center text-white">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Enhance Your Learning?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto text-blue-100">
                Join thousands of learners who are already using AI Learning Companion to study smarter.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="generator.php"
                    class="bg-white text-blue-600 px-8 py-4 rounded-lg font-bold text-lg hover:bg-gray-100 transition duration-300 shadow-lg">
                    Start Generating Free
                </a>
                <a href="#"
                    class="bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-white/10 transition duration-300">
                    View Demo
                </a>
            </div>
            <p class="mt-6 text-blue-200">No registration required to start</p>
        </section>
    </section>
</main>

<?php
// Include footer
include 'includes/footer.php';
?>