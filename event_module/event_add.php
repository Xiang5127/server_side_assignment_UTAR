<?php
// 1. Authentication & Database Connection
require_once '../includes/auth_check.php';
require_once '../config/db_conn.php'; // connect to database and get $conn

$user_id = $_SESSION['user_id'];
$error_msg = "";

// 2. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = $_POST['event_name'];
    $organiser = $_POST['organiser'];
    $event_date = $_POST['event_date'];
    $location = $_POST['location'];
    $location_type = $_POST['location_type']; // <-- Catch the new data
    $description = $_POST['description'];

    // Prepare MySQLi Statement
    $stmt = $conn->prepare("INSERT INTO events (user_id, event_name, organiser, event_date, location, location_type, description) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // "issssss" = integer, string, string, string, string, string, string
    $stmt->bind_param("issssss", $user_id, $event_name, $organiser, $event_date, $location, $location_type, $description);

    if ($stmt->execute()) {
        // redirect back to index with success message
        header("Location: event_index.php?status=added");
        exit();
    } else {
        $error_msg = "Error: Could not save the event. Please try again.";
        header("Location: event_index.php?status=error");
    }
    $stmt->close();
}

// 3. Load Header and Nav
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container">
    <div class="hero-box">
        <h1>Record New Event</h1>
        <p>Fill in the details below to add a new co-curricular activity to your record.</p>
    </div>

    <?php if ($error_msg) { ?>
        <div class="error-box">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php } ?>

    <form action="event_add.php" method="POST" onsubmit="return validateLocation()">
        <label for="event_name">Event Name</label>
        <input type="text" name="event_name" id="event_name" placeholder="e.g., UTAR Hackathon 2026" required>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <label for="organiser">Organiser</label>
                <input type="text" name="organiser" id="organiser"
                    placeholder="e.g., Faculty of Information and Communication Technology" required>
            </div>
            <div>
                <label for="event_date">Event Date</label>
                <input type="date" name="event_date" id="event_date" required>
            </div>
        </div>

        <!-- Location Type Selector -->
        <label style="margin-top: 22px;">Location</label>
        <div class="location-type-group">

            <!-- Online -->
            <label class="location-card" id="card-online" onclick="selectLocationType('online')">
                <input type="radio" name="location_type" value="online">
                <div class="location-card-title">Online / Virtual</div>
                <div class="location-card-desc">Zoom, Google Meet, Teams…</div>
            </label>

            <!-- Campus -->
            <label class="location-card" id="card-campus" onclick="selectLocationType('campus')">
                <input type="radio" name="location_type" value="campus">
                <div class="location-card-title">In Campus</div>
                <div class="location-card-desc">Select a campus venue</div>
            </label>

            <!-- Other -->
            <label class="location-card" id="card-other" onclick="selectLocationType('other')">
                <input type="radio" name="location_type" value="other">
                <div class="location-card-title">Other / Public Area</div>
                <div class="location-card-desc">Specify a location name</div>
            </label>

        </div>

        <!-- Display Dropdown Panel for Online (default display: none) -->
        <div id="panel-online" class="location-panel"
            style="display: none; margin-top: 20px; padding: 16px; background: var(--bg-layer-1); border-radius: var(--radius); border: 1px solid var(--border);">
            <label for="online_select" style="margin-top: 0;">Online Platform</label>
            <select id="online_select" onchange="updateFinalLocation()">
                <option value="" disabled selected hidden>-- Select a platform --</option>
                <option value="Zoom">Zoom</option>
                <option value="Google Meet">Google Meet</option>
                <option value="Microsoft Teams">Microsoft Teams</option>
                <option value="Youtube Live">Youtube Live</option>
                <option value="Other Online">Other</option>
            </select>
        </div>

        <!-- Display Dropdown Panel for Campus (default display: none) -->
        <div id="panel-campus" class="location-panel"
            style="display: none; margin-top: 20px; padding: 16px; background: var(--bg-layer-1); border-radius: var(--radius); border: 1px solid var(--border);">
            <label for="campus_select" style="margin-top: 0;">Campus Venue</label>
            <select id="campus_select" onchange="updateFinalLocation()">
                <option value="" disabled selected hidden>-- Select a venue --</option>
                <option value="Block A Heritage Hall">BLOCK A HERITAGE HALL</option>
                <option value="Block B Learning Complex 1">BLOCK B LEARNING COMPLEX 1</option>
                <option value="Block C Student Pavilion 1">BLOCK C STUDENT PAVILION 1</option>
                <option value="Block D Faculty of Science">BLOCK D FACULTY OF SCIENCE</option>
                <option value="Block E Faculty of Engineering and Green Technology">BLOCK E FACULTY OF ENGINEERING AND
                    GREEN TECHNOLOGY</option>
                <option value="Block F University Administration Block">BLOCK F UNIVERSITY ADMINISTRATION BLOCK</option>
                <option value="Block G Library">BLOCK G LIBRARY</option>
                <option value="Block H Faculty of Business and Finance">BLOCK H FACULTY OF BUSINESS AND FINANCE</option>
                <option value="Block I Lecture Complex 1">BLOCK I LECTURE COMPLEX 1</option>
                <option value="Block J Engineering Workshop">BLOCK J ENGINEERING WORKSHOP</option>
                <option value="Block K Student Pavilion 2">BLOCK K STUDENT PAVILION 2</option>
                <option value="Block L Lecture Complex 2">BLOCK L LECTURE COMPLEX 2</option>
                <option value="Block N FICT and IPSR Lab">BLOCK N FICT AND IPSR LAB</option>
                <option value="Block P FAS and ICS">BLOCK P FAS AND ICS</option>
                <option value="Block M Dewan Tun Dr. Ling Liong Sik">BLOCK M DEWAN TUN DR LING LIONG SIK</option>
            </select>
        </div>

        <!-- Display Textbox Panel for Others (default display: none) -->
        <div id="panel-other" class="location-panel"
            style="display: none; margin-top: 20px; padding: 16px; background: var(--bg-layer-1); border-radius: var(--radius); border: 1px solid var(--border);">
            <label for="other_input" style="margin-top: 0;">Specific Location</label>
            <input type="text" id="other_input" placeholder="e.g., KLCC Convention Centre"
                oninput="updateFinalLocation()">
        </div>

        <div id="location-type-error" class="location-type-hint" style="display:none;">
            ⚠️ Please select a location type before saving.
        </div>

        <!-- Hidden field sent to DB, php above will finally get this value via $_POST['location'] -->
        <input type="hidden" name="location" id="location_final">

        <label for="description">Description / Remarks</label>
        <textarea name="description" id="description" rows="5"
            placeholder="What was your role or key takeaway?"></textarea>

        <div style="margin-top: 24px; display: flex; gap: 12px;">
            <button type="submit" class="btn">Save Event</button>
            <a href="event_index.php" class="btn"
                style="background: var(--bg-layer-2); color: var(--text-main); box-shadow: none;">Cancel</a>
        </div>
    </form>
</div>


<!-- ── Styles ── -->
<style>
    .location-type-group {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-top: 6px;
    }

    .location-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 6px;
        padding: 16px 8px;
        border: 2px solid var(--border);
        border-radius: var(--radius);
        background: var(--bg-card);
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease, transform 0.15s ease;
        user-select: none;
    }

    .location-card input[type="radio"] {
        display: none;
    }

    .location-card:hover {
        border-color: var(--accent);
        background: var(--bg-layer-1);
        transform: translateY(-2px);
    }

    .location-card.selected {
        border-color: var(--primary);
        background: linear-gradient(135deg, var(--bg-layer-1), var(--bg-layer-2));
        box-shadow: 0 4px 16px rgba(139, 123, 94, 0.18);
        transform: translateY(-2px);
    }

    .location-card-title {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        font-weight: 700;
        font-size: 0.88rem;
        color: var(--text-main);
    }

    .location-card-desc {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        font-size: 0.76rem;
        color: var(--text-soft);
    }

    .location-type-hint {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        font-size: 0.84rem;
        color: var(--error-text);
        margin-top: 8px;
        padding: 8px 14px;
        background: var(--error-bg);
        border-radius: 10px;
        border: 1px solid #e7c4bd;
    }

    .btn-cancel {
        background: var(--bg-layer-2) !important;
        color: var(--text-main) !important;
        box-shadow: none !important;
    }

    .btn-cancel:hover {
        background: var(--border) !important;
        transform: translateY(-1px);
    }

    @media (max-width: 600px) {
        .location-type-group {
            grid-template-columns: 1fr;
        }
    }
</style>


<!-- ── Scripts ── -->
<script>
    function selectLocationType(type) {
        // 1. Manage Card Selection Styles
        // do this by first removing 'selected' class from all cards, then adding it to the clicked one
        // this is just for CSS
        document.querySelectorAll('.location-card').forEach(c => c.classList.remove('selected'));
        document.getElementById('card-' + type).classList.add('selected');
        document.getElementById('location-type-error').style.display = 'none';

        // 2. Hide ALL sub-panels first (reset state)
        // remove any previously selected panel to ensure only the relevant one is shown
        document.querySelectorAll('.location-panel').forEach(p => p.style.display = 'none');

        // 3. Show the specific sub-panel that matches the clicked type
        const activePanel = document.getElementById('panel-' + type);
        if (activePanel) {
            activePanel.style.display = 'block';
        }

        // 4. Force an update to the hidden input
        updateFinalLocation();
    }

    function updateFinalLocation() {
        // Check which card is currently active
        const activeCard = document.querySelector('.location-card.selected');
        if (!activeCard) return;

        const type = activeCard.querySelector('input[name="location_type"]').value;
        const finalInput = document.getElementById('location_final');

        // Route the data based on the active panel
        if (type === 'online') {
            finalInput.value = document.getElementById('online_select').value;
        } else if (type === 'campus') {
            finalInput.value = document.getElementById('campus_select').value;
        } else if (type === 'other') {
            finalInput.value = document.getElementById('other_input').value;
        }
    }

    function validateLocation() {
        const val = document.getElementById('location_final').value.trim();
        if (!val) {
            const errEl = document.getElementById('location-type-error');
            errEl.style.display = 'block';
            errEl.innerHTML = "⚠️ Please complete the location details for your selected option.";
            errEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        return true;
    }
</script>


<?php include '../includes/footer.php'; ?>