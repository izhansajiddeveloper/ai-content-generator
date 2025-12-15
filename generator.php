<?php
include 'includes/db.php';
include 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch categories and difficulty levels
$categories = mysqli_query($conn, "SELECT * FROM categories");
$levels = mysqli_query($conn, "SELECT * FROM difficulty_levels");

$output = '';
$output_id = '';

if (isset($_POST['generate'])) {
    $topic = $_POST['topic'];
    $category_id = $_POST['category'];
    $difficulty_id = $_POST['difficulty'];
    $output_type = $_POST['output_type'];

    // Get category and difficulty names
    $category_result = mysqli_query($conn, "SELECT category_name FROM categories WHERE id='$category_id'");
    $difficulty_result = mysqli_query($conn, "SELECT level_name FROM difficulty_levels WHERE id='$difficulty_id'");

    $category_name = mysqli_fetch_assoc($category_result)['category_name'];
    $difficulty_name = mysqli_fetch_assoc($difficulty_result)['level_name'];

    $user_id = $_SESSION['user_id'];

    // --- Always use AI generation ---
    include 'includes/openai.php';

    // Clean the input text
    $clean_topic = trim($topic);

    switch ($output_type) {
        case 'Summary':
            $prompt = "You are a professional summarizer. Summarize the following text into 3-5 concise sentences. 
            
IMPORTANT RULES:
1. DO NOT expand or add information
2. DO NOT include examples or explanations
3. DO NOT add any commentary
4. ONLY include the most important points
5. Keep sentences short and clear
6. Start directly with the summary, no introduction

Text to summarize: \"$clean_topic\"

Summary:";
            break;

        case 'Key Points':
            $prompt = "Extract the key points from the following text. 
Present them as clear bullet points. 
Focus on main ideas only. Do not add explanations.

Text: \"$clean_topic\"

Key Points:";
            break;

        case 'Full Assignment':
            $prompt = "Create a complete academic assignment based on the following text.
Category: $category_name
Difficulty: $difficulty_name

Include these sections:
1. Title
2. Introduction (brief overview)
3. Main Body (detailed content with sub-sections)
4. Conclusion (summary of key findings)
5. References (if applicable)

Text: \"$clean_topic\"

Assignment:";
            break;

        case 'Quiz':
            $prompt = "Create a quiz based on the following text. Generate 5 multiple-choice questions.

IMPORTANT FORMAT RULES:
1. Each question must be ONE LINE only
2. Each question must have EXACTLY 4 options (a, b, c, d)
3. One option must be correct
4. Format exactly like this:

Q1: [Question text here]?
a) [Option 1]
b) [Option 2]
c) [Option 3]
d) [Option 4]
Answer: [Correct option letter]

Q2: [Question text here]?
a) [Option 1]
b) [Option 2]
c) [Option 3]
d) [Option 4]
Answer: [Correct option letter]

Continue for 5 questions.

Text: \"$clean_topic\"

Quiz:";
            break;

        case 'Examples':
            $prompt = "Provide 3-5 clear examples based on the following text. 
For each example, give a brief explanation.
Use bullet points for each example.

Text: \"$clean_topic\"

Examples:";
            break;

        case 'Explanation for Kids':
            $prompt = "Explain the following text in a simple, engaging way suitable for children (ages 8-12).
Use simple language, short sentences, and fun analogies.
Include a relatable example.

Text: \"$clean_topic\"

Explanation for Kids:";
            break;

        case 'Explanation for Experts':
            $prompt = "Provide an expert-level explanation of the following text.
Include technical details, advanced concepts, and professional terminology.
Assume the reader has background knowledge in the field.

Text: \"$clean_topic\"

Expert Explanation:";
            break;

        default:
            $prompt = "Provide information about: \"$clean_topic\"";
            break;
    }

    // Generate AI content
    $output = generateAIContent($prompt);
    $output = trim($output);

    // For quiz, ensure proper formatting
    if ($output_type == 'Quiz') {
        $output = formatQuizOutput($output);
    }

    // --- Save to DB ---
    $topic_clean = mysqli_real_escape_string($conn, $topic);
    $output_clean = mysqli_real_escape_string($conn, $output);

    $insert_query = "INSERT INTO outputs (user_id, topic, category_id, difficulty_id, output_type, content) 
        VALUES ('$user_id', '$topic_clean', '$category_id', '$difficulty_id', '$output_type', '$output_clean')";

    if (mysqli_query($conn, $insert_query)) {
        $output_id = mysqli_insert_id($conn);
    }
}

// Function to format quiz output
function formatQuizOutput($quiz_text)
{
    $lines = explode("\n", $quiz_text);
    $formatted = [];
    $question_count = 0;

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        if (
            preg_match('/^(Q\d+|Question \d+)[:\s\.]/i', $line) ||
            (strpos($line, '?') !== false && $question_count < 5)
        ) {
            if (!preg_match('/^Q\d+/', $line)) {
                $question_count++;
                $line = "Q$question_count: " . ltrim($line, '0123456789. :');
            }
            $formatted[] = $line;
        } elseif (preg_match('/^[a-d]\)/', $line)) {
            $formatted[] = $line;
        } elseif (preg_match('/^Answer:/i', $line)) {
            $formatted[] = "Answer: " . trim(substr($line, strpos($line, ':') + 1));
        }
    }

    if (empty($formatted)) {
        return "Please ensure your quiz follows this format:\n\nQ1: [Your question here]?\na) Option 1\nb) Option 2\nc) Option 3\nd) Option 4\nAnswer: [correct letter]\n\n" . $quiz_text;
    }

    return implode("\n", $formatted);
}

// Function to format assignment output
function formatAssignmentOutput($text)
{
    $lines = explode("\n", $text);
    $formatted = '<div class="space-y-6">';
    $in_section = false;
    $section_num = 0;

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Detect main sections (Title, Introduction, etc.)
        if (preg_match('/^(#{1,3}|(\d+\.\s*))(.+)/', $line, $matches)) {
            $title = trim($matches[3]);
            if ($in_section) $formatted .= '</div>';

            $section_num++;
            $formatted .= '<div class="border-l-4 border-indigo-500 pl-4">';
            $formatted .= '<h3 class="text-xl font-bold text-gray-900 mb-3 flex items-center">';
            $formatted .= '<span class="w-8 h-8 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center mr-3 text-sm font-bold">' . $section_num . '</span>';
            $formatted .= htmlspecialchars($title);
            $formatted .= '</h3>';
            $in_section = true;
        }
        // Detect subsections
        elseif (preg_match('/^(\d+\.\d+)\s+(.+)/', $line, $matches)) {
            $formatted .= '<div class="ml-8 mb-4">';
            $formatted .= '<h4 class="text-lg font-semibold text-gray-800 mb-2">' . htmlspecialchars($matches[0]) . '</h4>';
        }
        // Detect bullet points
        elseif (strpos($line, '* ') === 0 || strpos($line, '- ') === 0) {
            $content = substr($line, 2);
            $formatted .= '<div class="flex items-start ml-6 mb-2">';
            $formatted .= '<span class="text-indigo-500 mr-2 mt-1">•</span>';
            $formatted .= '<span class="text-gray-700">' . htmlspecialchars($content) . '</span>';
            $formatted .= '</div>';
        }
        // Regular content
        else {
            $formatted .= '<p class="text-gray-700 mb-3 leading-relaxed">' . htmlspecialchars($line) . '</p>';
        }
    }

    if ($in_section) $formatted .= '</div>';
    $formatted .= '</div>';
    return $formatted;
}

// Function to format examples output
function formatExamplesOutput($text)
{
    $lines = explode("\n", $text);
    $formatted = '<div class="space-y-6">';
    $example_num = 0;

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Detect example headings
        if (
            preg_match('/(Example|Case|Scenario)\s*\d+/i', $line) ||
            preg_match('/^\d+\.\s*(.+)/', $line)
        ) {
            $example_num++;
            $formatted .= '<div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-100">';
            $formatted .= '<div class="flex items-center mb-4">';
            $formatted .= '<div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-4">';
            $formatted .= '<span class="text-white font-bold">' . $example_num . '</span>';
            $formatted .= '</div>';
            $formatted .= '<h4 class="text-lg font-bold text-gray-900">' . htmlspecialchars($line) . '</h4>';
            $formatted .= '</div>';
            $formatted .= '<div class="ml-14">';
        }
        // Detect bullet points
        elseif (strpos($line, '* ') === 0 || strpos($line, '- ') === 0) {
            $content = substr($line, 2);
            $formatted .= '<div class="flex items-start mb-2">';
            $formatted .= '<span class="text-purple-500 mr-3 mt-1">•</span>';
            $formatted .= '<p class="text-gray-700">' . htmlspecialchars($content) . '</p>';
            $formatted .= '</div>';
        }
        // End of example
        elseif (strlen($line) < 50 && $example_num > 0) {
            $formatted .= '</div></div>';
        }
        // Regular content
        else {
            $formatted .= '<p class="text-gray-700 mb-3">' . htmlspecialchars($line) . '</p>';
        }
    }

    if ($example_num > 0) $formatted .= '</div></div>';
    $formatted .= '</div>';
    return $formatted;
}

// Function to format kids explanation
function formatKidsExplanation($text)
{
    $lines = explode("\n", $text);
    $formatted = '<div class="bg-gradient-to-r from-pink-50 to-orange-50 rounded-xl p-6 border border-pink-200">';
    $formatted .= '<div class="flex items-center mb-6">';
    $formatted .= '<div class="w-12 h-12 bg-gradient-to-r from-pink-400 to-orange-400 rounded-full flex items-center justify-center mr-4">';
    $formatted .= '<span class="text-2xl">👧</span>';
    $formatted .= '</div>';
    $formatted .= '<h3 class="text-2xl font-bold text-gray-900">Kid-Friendly Explanation</h3>';
    $formatted .= '</div>';
    $formatted .= '<div class="space-y-4">';

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Simple paragraphs for kids
        $formatted .= '<p class="text-lg text-gray-800 leading-relaxed">';
        $formatted .= htmlspecialchars($line);
        $formatted .= '</p>';
    }

    $formatted .= '</div></div>';
    return $formatted;
}

// Function to format expert explanation
function formatExpertExplanation($text)
{
    $lines = explode("\n", $text);
    $formatted = '<div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl p-6 border border-gray-300">';
    $formatted .= '<div class="flex items-center mb-6">';
    $formatted .= '<div class="w-12 h-12 bg-gradient-to-r from-gray-600 to-blue-600 rounded-full flex items-center justify-center mr-4">';
    $formatted .= '<span class="text-2xl text-white">🎓</span>';
    $formatted .= '</div>';
    $formatted .= '<h3 class="text-2xl font-bold text-gray-900">Expert Analysis</h3>';
    $formatted .= '</div>';
    $formatted .= '<div class="space-y-4">';

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Technical paragraphs for experts
        $formatted .= '<p class="text-gray-800 leading-relaxed font-medium">';
        $formatted .= htmlspecialchars($line);
        $formatted .= '</p>';
    }

    $formatted .= '</div></div>';
    return $formatted;
}

// Function to format summary output
function formatSummaryOutput($text)
{
    $sentences = preg_split('/(?<=[.!?])\s+/', $text);
    $formatted = '<div class="space-y-4">';
    $count = 0;

    foreach ($sentences as $sentence) {
        $sentence = trim($sentence);
        if (empty($sentence)) continue;

        $count++;
        $formatted .= '<div class="flex items-start p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">';
        $formatted .= '<div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-4">';
        $formatted .= '<span class="text-blue-700 font-bold">' . $count . '</span>';
        $formatted .= '</div>';
        $formatted .= '<p class="text-gray-800 leading-relaxed flex-1">' . htmlspecialchars($sentence) . '</p>';
        $formatted .= '</div>';
    }

    $formatted .= '</div>';
    return $formatted;
}

// Function to format key points output
function formatKeyPointsOutput($text)
{
    $lines = explode("\n", $text);
    $formatted = '<div class="space-y-3">';

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Remove bullet markers if present
        $clean_line = preg_replace('/^[•\-\*]\s*/', '', $line);

        $formatted .= '<div class="flex items-start p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-100 hover:border-green-300 transition duration-300">';
        $formatted .= '<div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-4">';
        $formatted .= '<svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">';
        $formatted .= '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>';
        $formatted .= '</svg>';
        $formatted .= '</div>';
        $formatted .= '<p class="text-gray-800 font-medium">' . htmlspecialchars($clean_line) . '</p>';
        $formatted .= '</div>';
    }

    $formatted .= '</div>';
    return $formatted;
}
?>

<div class="min-h-screen bg-gradient-to-b from-gray-50 to-blue-50 py-8">
    <div class="max-w-6xl mx-auto px-4">

        <!-- Hero Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl mb-6 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6">
                AI Content
                <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Generator</span>
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Transform any text into professional summaries, quizzes, assignments, and more with intelligent AI processing.
            </p>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Column: Generator Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-2xl p-8">
                    <div class="flex items-center mb-10">
                        <div class="w-14 h-14 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-xl flex items-center justify-center mr-6 shadow-md">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold text-gray-900">Generate Content</h2>
                            <p class="text-gray-600 text-lg">Fill the form below and let AI do the work</p>
                        </div>
                    </div>

                    <form id="generateForm" method="POST" action="">
                        <!-- Topic Input -->
                        <div class="mb-10">
                            <label class="block text-gray-900 font-semibold mb-5 text-xl">
                                <span class="flex items-center">
                                    <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                    </svg>
                                    Your Content
                                </span>
                            </label>
                            <div class="relative">
                                <textarea name="topic" rows="8" required
                                    class="w-full p-6 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500 focus:border-blue-500 transition duration-300 text-gray-700 placeholder-gray-400 text-lg"
                                    placeholder="Paste or type your text here... Minimum 50 characters for best results"></textarea>
                                <div class="absolute bottom-4 right-4">
                                    <span id="charCount" class="text-sm font-medium text-gray-400">0 characters</span>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-base text-gray-500">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Provide detailed text (200+ characters) for optimal AI results
                            </div>
                        </div>

                        <!-- Settings Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                            <!-- Category Card -->
                            <div class="bg-gradient-to-br from-gray-50 to-blue-50 p-6 rounded-2xl border-2 border-gray-100">
                                <label class="block text-gray-900 font-semibold mb-4 text-lg">
                                    <span class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        Category
                                    </span>
                                </label>
                                <select name="category" required
                                    class="w-full p-4 bg-white border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-blue-500 focus:border-blue-500 transition duration-300 text-lg">
                                    <?php mysqli_data_seek($categories, 0); ?>
                                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?php echo $cat['id']; ?>">
                                            <?php echo htmlspecialchars($cat['category_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Difficulty Card -->
                            <div class="bg-gradient-to-br from-gray-50 to-green-50 p-6 rounded-2xl border-2 border-gray-100">
                                <label class="block text-gray-900 font-semibold mb-4 text-lg">
                                    <span class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                        Difficulty Level
                                    </span>
                                </label>
                                <select name="difficulty" required
                                    class="w-full p-4 bg-white border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-blue-500 focus:border-blue-500 transition duration-300 text-lg">
                                    <?php mysqli_data_seek($levels, 0); ?>
                                    <?php while ($lvl = mysqli_fetch_assoc($levels)): ?>
                                        <option value="<?php echo $lvl['id']; ?>">
                                            <?php echo htmlspecialchars($lvl['level_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Output Type Card -->
                            <div class="md:col-span-2 bg-gradient-to-br from-gray-50 to-purple-50 p-6 rounded-2xl border-2 border-gray-100">
                                <label class="block text-gray-900 font-semibold mb-4 text-lg">
                                    <span class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        Output Type
                                    </span>
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <?php
                                    $output_types = [
                                        'Summary' => ['icon' => '📋', 'color' => 'from-blue-500 to-blue-600', 'bg' => 'bg-blue-100 text-blue-800'],
                                        'Key Points' => ['icon' => '🔑', 'color' => 'from-green-500 to-green-600', 'bg' => 'bg-green-100 text-green-800'],
                                        'Quiz' => ['icon' => '❓', 'color' => 'from-yellow-500 to-yellow-600', 'bg' => 'bg-yellow-100 text-yellow-800'],
                                        'Examples' => ['icon' => '💡', 'color' => 'from-purple-500 to-purple-600', 'bg' => 'bg-purple-100 text-purple-800'],
                                        'Full Assignment' => ['icon' => '📝', 'color' => 'from-indigo-500 to-indigo-600', 'bg' => 'bg-indigo-100 text-indigo-800'],
                                        'Explanation for Kids' => ['icon' => '🧒', 'color' => 'from-pink-500 to-pink-600', 'bg' => 'bg-pink-100 text-pink-800'],
                                        'Explanation for Experts' => ['icon' => '🎓', 'color' => 'from-gray-500 to-gray-600', 'bg' => 'bg-gray-100 text-gray-800']
                                    ];
                                    ?>

                                    <select name="output_type" required class="hidden" id="outputTypeSelect">
                                        <?php foreach ($output_types as $type => $data): ?>
                                            <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <?php foreach ($output_types as $type => $data): ?>
                                        <button type="button"
                                            class="p-5 rounded-2xl border-2 border-transparent hover:border-blue-400 transition duration-300 flex flex-col items-center justify-center <?php echo $data['bg']; ?> hover:shadow-lg output-type-btn group"
                                            data-value="<?php echo $type; ?>">
                                            <div class="w-14 h-14 bg-gradient-to-br <?php echo $data['color']; ?> rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition duration-300">
                                                <span class="text-3xl"><?php echo $data['icon']; ?></span>
                                            </div>
                                            <span class="text-sm font-bold text-center"><?php echo $type; ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Generate Button -->
                        <button type="submit" name="generate"
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-bold py-5 px-8 rounded-2xl transition duration-300 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 text-xl">
                            <span class="flex items-center justify-center">
                                <svg id="generate-icon" class="w-7 h-7 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <svg id="loading-icon" class="hidden w-7 h-7 mr-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Generate AI Content
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column: Features & Preview -->
            <div class="space-y-8">
                <!-- AI Features Card -->
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl shadow-2xl p-8 text-white">
                    <h3 class="text-2xl font-bold mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        AI-Powered Features
                    </h3>
                    <ul class="space-y-5">
                        <li class="flex items-center text-lg">
                            <div class="w-10 h-10 bg-blue-500/30 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            Smart Text Analysis
                        </li>
                        <li class="flex items-center text-lg">
                            <div class="w-10 h-10 bg-blue-500/30 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            Context-Aware Generation
                        </li>
                        <li class="flex items-center text-lg">
                            <div class="w-10 h-10 bg-blue-500/30 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            Format Customization
                        </li>
                        <li class="flex items-center text-lg">
                            <div class="w-10 h-10 bg-blue-500/30 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            Quality Assurance
                        </li>
                    </ul>
                </div>

                <!-- Tips Card -->
                <div class="bg-white rounded-2xl shadow-2xl p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Pro Tips
                    </h3>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                                    <span class="text-blue-700 font-bold text-lg">1</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-700 font-medium">Provide detailed input text (200+ characters) for best results</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gradient-to-r from-green-100 to-green-200 rounded-xl flex items-center justify-center">
                                    <span class="text-green-700 font-bold text-lg">2</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-700 font-medium">Choose appropriate difficulty level based on your target audience</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gradient-to-r from-purple-100 to-purple-200 rounded-xl flex items-center justify-center">
                                    <span class="text-purple-700 font-bold text-lg">3</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-700 font-medium">Save generated content to your history for future reference</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loader (Initially Hidden) -->
                <div id="loader" class="hidden bg-white rounded-2xl shadow-2xl p-10 text-center">
                    <div class="flex flex-col items-center">
                        <div class="relative mb-8">
                            <svg class="animate-spin h-20 w-20 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">AI is Processing</h3>
                        <p class="text-gray-600 text-lg mb-6">Analyzing your text and generating content...</p>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-3 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Output Section -->
        <?php if ($output): ?>
            <div id="output" class="mt-12 animate-fade-in">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl shadow-2xl overflow-hidden border-2 border-blue-100">
                    <!-- Output Header -->
                    <div class="bg-white px-10 py-8 border-b border-gray-200">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                            <div class="flex items-center mb-6 lg:mb-0">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-r from-blue-100 to-indigo-100 flex items-center justify-center mr-6 shadow-md">
                                    <?php
                                    $icon = '📋';
                                    $icon_color = 'text-blue-600';
                                    switch ($_POST['output_type']) {
                                        case 'Summary':
                                            $icon = '📋';
                                            $icon_color = 'text-blue-600';
                                            break;
                                        case 'Key Points':
                                            $icon = '🔑';
                                            $icon_color = 'text-green-600';
                                            break;
                                        case 'Quiz':
                                            $icon = '❓';
                                            $icon_color = 'text-yellow-600';
                                            break;
                                        case 'Examples':
                                            $icon = '💡';
                                            $icon_color = 'text-purple-600';
                                            break;
                                        case 'Full Assignment':
                                            $icon = '📝';
                                            $icon_color = 'text-indigo-600';
                                            break;
                                        case 'Explanation for Kids':
                                            $icon = '🧒';
                                            $icon_color = 'text-pink-600';
                                            break;
                                        case 'Explanation for Experts':
                                            $icon = '🎓';
                                            $icon_color = 'text-gray-600';
                                            break;
                                    }
                                    ?>
                                    <span class="text-4xl <?php echo $icon_color; ?>"><?php echo $icon; ?></span>
                                </div>
                                <div>
                                    <h2 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($_POST['output_type']); ?></h2>
                                    <div class="flex flex-wrap gap-3 mt-3">
                                        <span class="px-4 py-2 bg-blue-100 text-blue-800 text-sm font-bold rounded-full shadow-sm">
                                            <?php echo htmlspecialchars($category_name); ?>
                                        </span>
                                        <span class="px-4 py-2 bg-green-100 text-green-800 text-sm font-bold rounded-full shadow-sm">
                                            <?php echo htmlspecialchars($difficulty_name); ?>
                                        </span>
                                        <span class="px-4 py-2 bg-gray-100 text-gray-800 text-sm font-bold rounded-full shadow-sm">
                                            <?php echo date('M j, Y'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex space-x-4">
                                <!-- Copy Button -->
                                <button onclick="copyToClipboard()"
                                    class="flex items-center px-5 py-3 bg-white border-2 border-gray-200 hover:border-blue-500 text-gray-800 font-bold rounded-xl transition duration-300 hover:bg-blue-50 shadow-md hover:shadow-lg">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Copy
                                </button>

                                <!-- Download PDF Button -->
                                <?php if ($output_id): ?>
                                    <a href="download.php?id=<?php echo $output_id; ?>" target="_blank"
                                        class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-xl transition duration-300 shadow-lg hover:shadow-xl">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Download PDF
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Output Content -->
                    <div class="p-10">
                        <div class="bg-white rounded-2xl border-2 border-gray-100 p-8 shadow-sm">
                            <div class="prose max-w-none">
                                <?php
                                switch ($_POST['output_type']) {
                                    case 'Quiz':
                                        echo formatQuizDisplay($output);
                                        break;
                                    case 'Full Assignment':
                                        echo formatAssignmentOutput($output);
                                        break;
                                    case 'Examples':
                                        echo formatExamplesOutput($output);
                                        break;
                                    case 'Explanation for Kids':
                                        echo formatKidsExplanation($output);
                                        break;
                                    case 'Explanation for Experts':
                                        echo formatExpertExplanation($output);
                                        break;
                                    case 'Summary':
                                        echo formatSummaryOutput($output);
                                        break;
                                    case 'Key Points':
                                        echo formatKeyPointsOutput($output);
                                        break;
                                    default:
                                        echo '<div class="text-gray-800 leading-relaxed text-lg">' . nl2br(htmlspecialchars($output)) . '</div>';
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Stats Footer -->
                        <div class="mt-8 pt-8 border-t border-gray-200">
                            <div class="flex flex-col lg:flex-row justify-between items-center">
                                <div class="flex items-center text-gray-600 mb-4 lg:mb-0">
                                    <svg class="w-6 h-6 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-lg">Generated <?php echo date('g:i A'); ?></span>
                                </div>
                                <div class="flex items-center space-x-8">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="text-lg font-medium"><?php echo str_word_count($output); ?> words</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                                        </svg>
                                        <span class="text-lg font-medium"><?php echo ceil(str_word_count($output) / 200); ?> min read</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Character counter for textarea
    const textarea = document.querySelector('textarea[name="topic"]');
    const charCount = document.getElementById('charCount');

    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length + ' characters';

            if (length < 50) {
                charCount.classList.remove('text-green-600', 'text-yellow-500');
                charCount.classList.add('text-red-500');
            } else if (length < 200) {
                charCount.classList.remove('text-red-500', 'text-green-600');
                charCount.classList.add('text-yellow-500');
            } else {
                charCount.classList.remove('text-red-500', 'text-yellow-500');
                charCount.classList.add('text-green-600');
            }
        });

        textarea.dispatchEvent(new Event('input'));
    }

    // Output type selection
    const outputTypeBtns = document.querySelectorAll('.output-type-btn');
    const outputTypeSelect = document.getElementById('outputTypeSelect');

    if (outputTypeBtns.length && outputTypeSelect) {
        outputTypeBtns[0].classList.add('border-blue-500', 'border-2');
        outputTypeSelect.value = outputTypeBtns[0].dataset.value;

        outputTypeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                outputTypeBtns.forEach(b => {
                    b.classList.remove('border-blue-500', 'border-2');
                });
                this.classList.add('border-blue-500', 'border-2');
                outputTypeSelect.value = this.dataset.value;
            });
        });
    }

    // Form submission
    const form = document.getElementById('generateForm');
    const loader = document.getElementById('loader');
    const outputDiv = document.getElementById('output');
    const generateIcon = document.getElementById('generate-icon');
    const loadingIcon = document.getElementById('loading-icon');

    if (form) {
        form.addEventListener('submit', function(e) {
            const textarea = form.querySelector('textarea[name="topic"]');
            if (textarea.value.trim().length < 50) {
                e.preventDefault();
                alert('Please enter at least 50 characters for better AI results.');
                textarea.focus();
                return;
            }

            loader.classList.remove('hidden');
            generateIcon.classList.add('hidden');
            loadingIcon.classList.remove('hidden');
            if (outputDiv) outputDiv.classList.add('hidden');

            loader.scrollIntoView({
                behavior: 'smooth'
            });
        });
    }

    // Copy to clipboard function
    function copyToClipboard() {
        const outputText = document.querySelector('#output .text-gray-800, #output .prose');
        if (!outputText) return;

        const textToCopy = outputText.textContent || outputText.innerText;

        navigator.clipboard.writeText(textToCopy)
            .then(() => {
                const button = event.target.closest('button');
                if (!button) return;

                const originalText = button.innerHTML;
                button.innerHTML = `
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Copied!
                `;
                button.classList.remove('bg-white', 'border-gray-200', 'text-gray-800', 'hover:bg-blue-50');
                button.classList.add('bg-green-100', 'border-green-300', 'text-green-800');

                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-100', 'border-green-300', 'text-green-800');
                    button.classList.add('bg-white', 'border-gray-200', 'text-gray-800', 'hover:bg-blue-50');
                }, 2000);
            })
            .catch(err => {
                console.error('Failed to copy: ', err);
                alert('Failed to copy text. Please select and copy manually.');
            });
    }
</script>

<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.8s ease-out;
    }

    .output-type-btn {
        transition: all 0.3s ease;
    }

    .output-type-btn:hover {
        transform: translateY(-3px);
    }

    .prose {
        max-width: none !important;
    }

    .prose p {
        margin-bottom: 1rem;
        line-height: 1.7;
    }

    .prose h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }

    .prose h4 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
</style>

<?php
// Function to format quiz display with better styling
function formatQuizDisplay($quiz_text)
{
    $lines = explode("\n", $quiz_text);
    $formatted = '';
    $in_question = false;

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        if (preg_match('/^Q\d+:/i', $line)) {
            if ($in_question) $formatted .= "</div>\n";
            $formatted .= "<div class='mb-10 p-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border-2 border-blue-200 shadow-sm'>\n";
            $formatted .= "<div class='font-bold text-2xl text-gray-900 mb-6 flex items-center'>\n";
            $formatted .= "<span class='w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl flex items-center justify-center mr-5 text-lg font-bold'>" . substr($line, 1, 1) . "</span>\n";
            $formatted .= "<span>" . htmlspecialchars(substr($line, strpos($line, ':') + 1)) . "</span>\n";
            $formatted .= "</div>\n<div class='ml-16 space-y-4'>\n";
            $in_question = true;
        } elseif (preg_match('/^[a-d]\)/', $line)) {
            $option_letter = substr($line, 0, 1);
            $option_text = substr($line, 3);
            $formatted .= "<div class='flex items-center p-4 bg-white rounded-xl border-2 border-gray-100 hover:border-blue-300 transition duration-300 shadow-sm'>\n";
            $formatted .= "<span class='w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-700 font-bold text-lg mr-4 border border-gray-200'>" . $option_letter . "</span>\n";
            $formatted .= "<span class='text-gray-800 text-lg'>" . htmlspecialchars($option_text) . "</span>\n";
            $formatted .= "</div>\n";
        } elseif (preg_match('/^Answer:/i', $line)) {
            $answer = trim(substr($line, strpos($line, ':') + 1));
            $formatted .= "</div>\n";
            $formatted .= "<div class='mt-8 p-6 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border-2 border-green-200 shadow-sm'>\n";
            $formatted .= "<div class='flex items-center'>\n";
            $formatted .= "<div class='w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center mr-4'>\n";
            $formatted .= "<svg class='w-6 h-6 text-white' fill='currentColor' viewBox='0 0 20 20'>\n";
            $formatted .= "<path fill-rule='evenodd' d='M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' clip-rule='evenodd'/>\n";
            $formatted .= "</svg>\n";
            $formatted .= "</div>\n";
            $formatted .= "<div>\n";
            $formatted .= "<h4 class='font-bold text-green-800 text-xl'>Correct Answer</h4>\n";
            $formatted .= "<p class='text-green-700 text-lg font-medium'>" . htmlspecialchars($answer) . "</p>\n";
            $formatted .= "</div>\n";
            $formatted .= "</div>\n</div>\n</div>\n";
            $in_question = false;
        } else {
            $formatted .= "<div class='text-gray-700 text-lg'>" . htmlspecialchars($line) . "</div>\n";
        }
    }

    if ($in_question) $formatted .= "</div>\n";

    return $formatted;
}

include 'includes/footer.php';
