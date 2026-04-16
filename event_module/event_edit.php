<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$error_msg = "";
$event = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = (int) ($_POST['event_id'] ?? 0);
    $event_name = trim($_POST['event_name'] ?? '');
    $organiser = trim($_POST['organiser'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');
    $location_type = trim($_POST['location_type'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($event_name) || empty($organiser) || empty($event_date) || empty($location_type) || empty($location)) {
        $error_msg = "Please complete all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE events SET event_name=?, organiser=?, event_date=?, location_type=?, location=?, description=? WHERE event_id=? AND user_id=?");

        if ($stmt) {
            $stmt->bind_param("ssssssii", $event_name, $organiser, $event_date, $location_type, $location, $description, $event_id, $user_id);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: event_index.php?status=updated");
                exit();
            } else {
                $error_msg = "Error: Could not update the event. Please try again.";
            }

            $stmt->close();
        } else {
            $error_msg = "Database error: Unable to prepare update query.";
        }
    }
} else {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: event_index.php");
        exit();
    }

    $event_id = (int) $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND user_id = ?");

    if ($stmt) {
        $stmt->bind_param("ii", $event_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();
        $stmt->close();
    }

    if (!$event) {
        header("Location: event_index.php");
        exit();
    }
}

include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container">
    <div class="hero-box">
        <h1>Edit Event</h1>
        <p>Update the details of your co-curricular record.</p>
    </div>

    <?php if (!empty($error_msg)) { ?>
        <div class="error-box">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php } ?>

    <form action="event_edit.php?id=<?= $event['event_id'] ?>" method="POST" onsubmit="return validateLocation()">
        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">

        <label for="event_name">Event Name</label>
        <input type="text" name="event_name" id="event_name" required value="<?= htmlspecialchars($event['event_name']) ?>">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <label for="organiser">Organiser</label>
                <input type="text" name="organiser" id="organiser" required value="<?= htmlspecialchars($event['organiser']) ?>">
            </div>
            <div>
                <label for="event_date">Event Date</label>
                <input type="date" name="event_date" id="event_date" required value="<?= htmlspecialchars($event['event_date']) ?>">
            </div>
        </div>

        <label style="margin-top: 22px;">Location</label>

        <div class="location-type-group">
            <label class="location-card" id="card-online" onclick="selectLocationType('online')">
                <input type="radio" name="location_type" value="online">
                <div class="location-card-title">Online / Virtual</div>
                <div class="location-card-desc">Zoom, Google Meet, Teams…</div>
            </label>

            <label class="location-card" id="card-campus" onclick="selectLocationType('campus')">
                <input type="radio" name="location_type" value="campus">
                <div class="location-card-title">In Campus</div>
                <div class="location-card-desc">Select a campus venue</div>
            </label>

            <label class="location-card" id="card-other" onclick="selectLocationType('other')">
                <input type="radio" name="location_type" value="other">
                <div class="location-card-title">Other / Public Area</div>
                <div class="location-card-desc">Specify a location name</div>
            </label>
        </div>

        <div id="panel-online" class="location-panel" style="display: none; margin-top: 20px; padding: 16px; background: var(--bg-soft); border-radius: 16px; border: 1px solid var(--border);">
            <label for="online_select" style="margin-top: 0;">Online Platform</label>
            <select id="online_select" onchange="updateFinalLocation()">
                <option value="" disabled selected hidden>-- Select a platform --</option>
                <option value="Zoom">Zoom</option>
                <option value="Google Meet">Google Meet</option>
                <option value="Microsoft Teams">Microsoft Teams</option>
                <option value="YouTube Live">YouTube Live</option>
                <option value="Other Online">Other</option>
            </select>
        </div>

        <div id="panel-campus" class="location-panel" style="display: none; margin-top: 20px; padding: 16px; background: var(--bg-soft); border-radius: 16px; border: 1px solid var(--border);">
            <label for="campus_select" style="margin-top: 0;">Campus Venue</label>
            <select id="campus_select" onchange="updateFinalLocation()">
                <option value="" disabled selected hidden>-- Select a venue --</option>
                <option value="Block A Heritage Hall">BLOCK A HERITAGE HALL</option>
                <option value="Block B Learning Complex 1">BLOCK B LEARNING COMPLEX 1</option>
                <option value="Block C Student Pavilion 1">BLOCK C STUDENT PAVILION 1</option>
                <option value="Block D Faculty of Science">BLOCK D FACULTY OF SCIENCE</option>
                <option value="Block E Faculty of Engineering and Green Technology">BLOCK E FACULTY OF ENGINEERING AND GREEN TECHNOLOGY</option>
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

        <div id="panel-other" class="location-panel" style="display: none; margin-top: 20px; padding: 16px; background: var(--bg-soft); border-radius: 16px; border: 1px solid var(--border);">
            <label for="other_input" style="margin-top: 0;">Specific Location</label>
            <input type="text" id="other_input" placeholder="e.g. KLCC Convention Centre" oninput="updateFinalLocation()">
        </div>

        <div id="location-type-error" class="location-type-hint" style="display:none;">
            Please complete the location details for your selected option.
        </div>

        <input type="hidden" name="location" id="location_final" value="<?= htmlspecialchars($event['location']) ?>">

        <label for="description">Description / Remarks</label>
        <textarea name="description" id="description" rows="5"><?= htmlspecialchars($event['description']) ?></textarea>

        <div class="form-actions">
            <button type="submit" class="btn">Update Event</button>
            <a href="event_index.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

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
        border-radius: 16px;
        background: rgba(255,255,255,0.75);
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease, transform 0.15s ease;
        user-select: none;
    }

    .location-card input[type="radio"] {
        display: none;
    }

    .location-card:hover {
        border-color: var(--accent);
        background: rgba(255,255,255,0.92);
        transform: translateY(-2px);
    }

    .location-card.selected {
        border-color: var(--primary);
        background: linear-gradient(135deg, var(--bg-soft), var(--bg-layer));
        box-shadow: 0 8px 18px rgba(139, 117, 88, 0.14);
        transform: translateY(-2px);
    }

    .location-card-title {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text-main);
    }

    .location-card-desc {
        font-size: 0.78rem;
        color: var(--text-soft);
    }

    .location-type-hint {
        font-size: 0.84rem;
        color: var(--error-text);
        margin-top: 8px;
        padding: 8px 14px;
        background: var(--error-bg);
        border-radius: 10px;
        border: 1px solid rgba(141, 86, 78, 0.16);
    }

    @media (max-width: 600px) {
        .location-type-group {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    function selectLocationType(type) {
        document.querySelectorAll('.location-card').forEach(c => c.classList.remove('selected'));
        document.getElementById('card-' + type).classList.add('selected');
        document.getElementById('location-type-error').style.display = 'none';

        document.querySelectorAll('.location-panel').forEach(p => p.style.display = 'none');

        const activePanel = document.getElementById('panel-' + type);
        if (activePanel) {
            activePanel.style.display = 'block';
        }

        const radio = document.querySelector('#card-' + type + ' input[name="location_type"]');
        if (radio) {
            radio.checked = true;
        }

        updateFinalLocation();
    }

    function updateFinalLocation() {
        const activeCard = document.querySelector('.location-card.selected');
        if (!activeCard) return;

        const type = activeCard.querySelector('input[name="location_type"]').value;
        const finalInput = document.getElementById('location_final');

        if (type === 'online') {
            finalInput.value = document.getElementById('online_select').value;
        } else if (type === 'campus') {
            finalInput.value = document.getElementById('campus_select').value;
        } else if (type === 'other') {
            finalInput.value = document.getElementById('other_input').value;
        }
    }

    function validateLocation() {
        const selectedType = document.querySelector('input[name="location_type"]:checked');
        const val = document.getElementById('location_final').value.trim();

        if (!selectedType || !val) {
            const errEl = document.getElementById('location-type-error');
            errEl.style.display = 'block';
            errEl.innerHTML = "Please complete the location details for your selected option.";
            errEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        return true;
    }

    document.addEventListener("DOMContentLoaded", function () {
        const savedLocType = <?= json_encode($event['location_type'] ?? '') ?>;
        const savedDbLocation = <?= json_encode($event['location'] ?? '') ?>;

        if (savedLocType) {
            selectLocationType(savedLocType);

            if (savedLocType === 'online') {
                document.getElementById('online_select').value = savedDbLocation;
            } else if (savedLocType === 'campus') {
                document.getElementById('campus_select').value = savedDbLocation;
            } else if (savedLocType === 'other') {
                document.getElementById('other_input').value = savedDbLocation;
            }
        }

        updateFinalLocation();
    });
</script>

<?php include '../includes/footer.php'; ?>