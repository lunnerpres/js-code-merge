<?php
session_start();
include_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
$user_id = $_SESSION["id"];

// Mark user as ACTIVE when they access the app
$stmt = $link->prepare("UPDATE users SET status='Active', last_seen=NOW() WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
?>

<script>
function updatePresence() {
    fetch('update_status.php', {
        method: 'POST',
        credentials: 'include'
    });
}

// send every 20 seconds
setInterval(updatePresence, 1000);

// also send immediately on page load
updatePresence();

setInterval(() => {
    fetch('presence_cleanup.php');
}, 60000);
</script>

<?php
require_once "config.php";

// Arrays
$labels = [];
$births = [];
$deaths = [];
$permits_in = [];
$permits_out = [];
$on_register = [];

$sql = "SELECT * FROM monthly_stats ORDER BY FIELD(month,
        'Jan','Feb','Mar','Apr','May','Jun')";

$result = $link->query($sql);

while ($row = $result->fetch_assoc()) {

    $labels[] = $row['month'];
    $births[] = (int)$row['births'];
    $deaths[] = (int)$row['deaths'];
    $permits_in[] = (int)$row['permits_in'];
    $permits_out[] = (int)$row['permits_out'];
    $on_register[] = (int)$row['on_register'];
}
?>
<?php
$currentUser = $_SESSION["id"];

/* Get last read announcement */
$readQuery = mysqli_query(
    $link,
    "SELECT last_read_announcement
     FROM announcement_reads
     WHERE user_id = '$currentUser'
     LIMIT 1"
);

$lastRead = 0;

if(mysqli_num_rows($readQuery) > 0)
{
    $readRow = mysqli_fetch_assoc($readQuery);
    $lastRead = $readRow['last_read_announcement'];
}

/* Count unread announcements */
$countQuery = mysqli_query(
    $link,
    "SELECT COUNT(*) AS total
     FROM announcements
     WHERE id > '$lastRead'"
);

$countRow = mysqli_fetch_assoc($countQuery);

$unreadAnnouncements = $countRow['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Livestock Registers | Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="styles.css">

    <style>

        /* ================= GLOBAL ================= */
        a{
            text-decoration:none;
        }
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Poppins',sans-serif;
        }

        html,
        body{
            width:100%;
            height:100%;
            overflow:hidden;
        }

        body{
            background:
                    linear-gradient(
                            135deg,
                            #0f172a 0%,
                            #1d4ed8 45%,
                            #ef4444 100%
                    );

            color:white;
        }

        /* ================= NAVBAR ================= */

        .navbar{
            width:100%;
            height:80px;

            display:flex;
            justify-content:space-between;
            align-items:center;
	    position:relative;
    	    z-index:100000;
            padding:0 30px;

            background:rgba(0,0,0,0.18);
            backdrop-filter:blur(12px);

            border-bottom:1px solid rgba(255,255,255,0.08);
        }

        /* LEFT */

        .logo-section{
            display:flex;
            align-items:center;
            gap:6px;
        }

        .logo-section img{
            height:44px;
            width:auto;
            object-fit:contain;
            display:block;
        }


        /* RIGHT */

        .top-icons{
            display:flex;
            align-items:center;
            gap:20px;
        }

        /* ICONS */

        .icon-box{
            width:45px;
            height:45px;

            border-radius:14px;

            background:rgba(255,255,255,0.1);

            display:flex;
            justify-content:center;
            align-items:center;

            cursor:pointer;

            transition:0.3s;
            position:relative;

            backdrop-filter:blur(10px);
        }

        .icon-box:hover{
            transform:translateY(-2px);
            background:rgba(255,255,255,0.18);
        }

        .icon-box i{
            font-size:18px;
            color:white;
        }

        /* BADGES */

        .badge{
            position:absolute;
            top:-5px;
            right:-5px;

            width:18px;
            height:18px;

            border-radius:50%;

            background:#ff3b5c;

            display:flex;
            justify-content:center;
            align-items:center;

            font-size:10px;
            font-weight:bold;
        }

        /* PROFILE */

        .profile{
            display:flex;
            align-items:center;
            gap:10px;

            padding:8px 14px;

            border-radius:16px;

            background:rgba(255,255,255,0.1);

            backdrop-filter:blur(10px);
        }

        .profile img{
            width:38px;
            height:38px;
            border-radius:50%;
        }

        .profile small{
            color:rgba(255,255,255,0.7);
        }

        /* ================= MAIN LAYOUT ================= */

        .container{
            display:flex;
            height:calc(100vh - 80px);
            overflow:hidden;
        }

        /* ================= SIDEBAR ================= */

        .sidebar{
            width:260px;

            padding:20px;

            background:rgba(0,0,0,0.15);

            backdrop-filter:blur(12px);

            border-right:1px solid rgba(255,255,255,0.08);
        }

        /* MENU TITLE */

        .menu-title{
            color:rgba(255,255,255,0.7);

            font-size:13px;

            margin-bottom:20px;
            padding-left:10px;
        }

        /* MENU LINKS */

        .sidebar a{
            display:flex;
            align-items:center;
            gap:15px;

            padding:15px;

            border-radius:16px;

            text-decoration:none;
            color:white;

            margin-bottom:12px;

            transition:0.3s;

            background:rgba(255,255,255,0.05);
        }

        .sidebar a:hover{
            background:rgba(255,255,255,0.14);
            transform:translateX(5px);
        }

        .sidebar a i{
            width:20px;
        }

        /* ================= CONTENT ================= */

.content{
    flex:1;
    padding:20px;
    overflow:hidden;
    position:relative;
}
        /* ================= GRID ================= */

.dashboard-grid{
    display:grid;
    grid-template-columns:420px 1fr;
    gap:30px;
    height:100%;
    align-items:stretch;
}

        /* ================= CARDS ================= */

        .card{
            height:100%;

            background:rgba(255,255,255,0.08);

            backdrop-filter:blur(18px);

            border:1px solid rgba(255,255,255,0.08);

            border-radius:24px;

            padding:18px;

            overflow:hidden;

            box-shadow:
                    0 10px 25px rgba(0,0,0,0.25);
        }

        /* TITLES */

        .card-title{
            font-size:20px;
            font-weight:600;

            margin-bottom:15px;

            color:white;
        }

        /* ================= IFRAMES ================= */

        .notes-box,
        .calendar-box{
            height:calc(100vh - 160px);

            overflow:hidden;

            border-radius:18px;
        }

        .notes-box iframe,
        .calendar-box iframe{
            width:100%;
            height:100%;

            border:none;

            border-radius:18px;

            background:white;
        }

        /* ================= LOGOUT ================= */

            .logout-btn{
                display:flex;
                align-items:center;
                justify-content:center;

                min-width:110px;
                height:48px;

                padding:0 22px;

                border-radius:16px;

                background:
                        linear-gradient(
                                135deg,
                                #2563eb,
                                #ff3b5c
                        );

                color:white !important;

                text-decoration:none;

                font-size:15px;
                font-weight:600;

                transition:all 0.3s ease;

                box-shadow:
                        0 8px 20px rgba(0,0,0,0.25);
            }

            .logout-btn:hover{
                transform:translateY(-3px);

                background:
                        linear-gradient(
                                135deg,
                                #3b82f6,
                                #ff4d6d
                        );

                box-shadow:
                        0 12px 24px rgba(0,0,0,0.35);
            }

        /* ================= MOBILE ================= */

        @media(max-width:1000px){

            .dashboard-grid{
                grid-template-columns:1fr;
                overflow-y:auto;
            }

            .sidebar{
                width:85px;
            }

            .sidebar a span{
                display:none;
            }

            .logo-text{
                display:none;
            }

            .notes-box,
            .calendar-box{
                height:450px;
            }
        }
       

        /* ================= DELETE BUTTON ================= */

       
.profile-icon{
    width:38px;
    height:38px;
    border-radius:50%;

    display:flex;
    align-items:center;
    justify-content:center;

    background:rgba(255,255,255,0.15);

    color:#ffffff;
    font-size:18px;

    border:1px solid rgba(255,255,255,0.15);
}

/* ================= HEADER ================= */

.popup-header{
    display:flex;
    justify-content:space-between;
    align-items:center;

    margin-bottom:20px;
    padding-bottom:15px;

    border-bottom:1px solid rgba(255,255,255,0.08);
}

.popup-header h2{
    color:#ffffff;
    font-size:15px;
    font-weight:200;
}



/* ================= TABLE ================= */

.summary-table,
.phpmyadmin-table{
    width:100%;
    border-collapse:collapse;

    background:#111827;

    border-radius:12px;
    overflow:hidden;
}

/* Header */

.summary-table th,
.phpmyadmin-table th{
    background:linear-gradient(
        180deg,
        #2563eb,
        #1d4ed8
    );

    color:white;

    padding:14px;

    text-align:left;

    border:1px solid #1e40af;
}

/* Rows */

.summary-table td,
.phpmyadmin-table td{
    padding:12px;

    color:#f8fafc;

    border:1px solid #334155;
}

/* Alternate rows */

.summary-table tr:nth-child(even),
.phpmyadmin-table tr:nth-child(even){
    background:#1e293b;
}

.summary-table tr:nth-child(odd),
.phpmyadmin-table tr:nth-child(odd){
    background:#0f172a;
}

/* Hover */

.summary-table tr:hover,
.phpmyadmin-table tr:hover{
    background:#334155;
}

/* ================= SCROLL ================= */

.table-wrapper{
    max-height:500px;
    overflow:auto;
}

.table-wrapper::-webkit-scrollbar{
    width:10px;
}

.table-wrapper::-webkit-scrollbar-thumb{
    background:#2563eb;
    border-radius:20px;
}

#pageLoader{
    position:fixed;

    top:80px;      /* navbar height */
    left:260px;    /* after sidebar */

    right:0;
    bottom:0;

    background:rgba(15,23,42,0.12);

    backdrop-filter:blur(2px);

    display:none;

    justify-content:center;
    align-items:center;

    z-index:999;
}

/* 3D CARD */

.loader-card{

    width:220px;
    height:56px;

    display:flex;
    align-items:center;
    justify-content:center;
    gap:12px;

    border-radius:14px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,0.14),
            rgba(255,255,255,0.06)
        );

    border:1px solid rgba(255,255,255,0.12);

    backdrop-filter:blur(16px);

    box-shadow:
        0 10px 25px rgba(0,0,0,0.35),
        inset 0 1px 0 rgba(255,255,255,0.15);
}
/* TEXT */

.loader-label{

    color:#ffffff;

    font-size:14px;
    font-weight:500;

    letter-spacing:0.3px;
}
/* SMALL SPINNER */

.mini-spinner{

    width:16px;
    height:16px;

    border:2px solid rgba(255,255,255,0.20);
    border-top:2px solid #ffffff;

    border-radius:50%;

    animation:spin .7s linear infinite;
}

@keyframes spin{
    from{
        transform:rotate(0deg);
    }
    to{
        transform:rotate(360deg);
    }
}
.notifications-header{
    padding:12px 16px;
    border-bottom:1px solid rgba(255,255,255,.08);
    font-size:14px;
    font-weight:600;
    color:#e5e7eb;
    background:#111827;
    display:flex;
    justify-content:space-between;
    align-items:center;

}
.notifications-header h3{
    margin:0;
    font-size:15px;
    font-weight:600;
}

.notifications-header i{
    margin-right:8px;
}
.notifications-body{
    padding:12px;
    max-height:500px;
    overflow-y:auto;
}
.notification-card{
    background:#1f2937;
    border:1px solid rgba(255,255,255,.05);
    border-radius:12px;
    padding:12px;
    margin-bottom:10px;
    transition:.2s;
    cursor:pointer;
}

.notification-card:hover{
    background:#263244;
    transform:translateY(-1px);
}
.notification-title{
    font-size:13px;
    font-weight:600;
    color:#f9fafb;
    margin-bottom:4px;
}

.notification-message{
    font-size:12px;
    color:#9ca3af;
    line-height:1.5;
    margin-bottom:8px;
}

.notification-time{
    font-size:11px;
    color:#6b7280;
}
.notification-card.unread{
    border-left:4px solid #2563eb;
}

.notification-card.unread .notification-title{
    color:#ffffff;
}
.notifications-header button{
    background:none;
    border:none;
    font-size:24px;
    color:#94a3b8;
    cursor:pointer;
    padding:0;
    margin:0;
    line-height:1;
}
       
/* ================= MESSAGES PANEL ================= */

.messages-panel{

    position:fixed;

    right:20px;
    bottom:-450px;

    width:350px;
    height:450px;

    background:#1e293b;

    border-radius:15px 15px 0 0;

    transition:0.4s;

    z-index:99999;

    color:white;
}

.messages-panel.active{

    bottom:0;
}

.messages-header{

    height:60px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    padding:0 15px;

    background:#0f172a;
}
.messages-user{

    display:flex;
    align-items:center;
    gap:10px;
}

.messages-user img{

    width:40px;
    height:40px;

    border-radius:50%;
}

.messages-user span{

    color:white;
    font-weight:600;
}

.messages-header button{

    border:none;
    background:none;

    color:white;

    font-size:18px;

    cursor:pointer;
}

.messages-list{

    padding:8px;

    height:370px;

    overflow-y:auto;
}
.message-item{

    display:flex;
    align-items:center;

    gap:10px;

    padding:8px 10px;

    background:rgba(255,255,255,.05);

    margin-bottom:4px;

    border-radius:10px;

    cursor:pointer;

    transition:0.2s;
}

.message-item:hover{

    background:rgba(255,255,255,.10);
}

.message-avatar{

    position:relative;

    width:38px;
    height:38px;

    border-radius:50%;

    background:rgba(255,255,255,.12);

    display:flex;
    align-items:center;
    justify-content:center;

    flex-shrink:0;

    overflow:visible;
}

.message-avatar i{

    font-size:18px;

    color:rgba(255,255,255,.85);
}


/*=========================================
ONLINE / OFFLINE STATUS DOT
=========================================*/

.user-status-dot{

    position:absolute;

    width:10px;
    height:10px;

    border-radius:50%;

    bottom:-1px;
    right:-1px;

    border:2px solid #1a1a1a;

    box-sizing:border-box;

    transition:.25s;
}

/* Online */

.user-status-dot.online{

    background:#2ecc71;

    box-shadow:0 0 6px rgba(46,204,113,.7);
}

/* Offline */

.user-status-dot.offline{

    background:#8d8d8d;
}

.message-details{

    flex:1;

    overflow:hidden;
}

.message-name{

    font-size:12px;

    font-weight:600;

    color:#ffffff;

    margin-bottom:2px;
}

.message-preview{

    font-size:11px;

    color:rgba(255,255,255,.65);

    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.message-item strong{

    display:block;

    color:white;

    font-size:12px;

    font-weight:600;

    margin-bottom:1px;

    line-height:1.2;
}

.message-item p{

    color:rgba(255,255,255,0.65);

    font-size:11px;

    line-height:1.2;

    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;

    margin:0;
}
.messages-actions{

    display:flex;
    align-items:center;

    gap:6px;
}

.header-btn{

    width:32px;
    height:32px;

    border:none;

    border-radius:8px;

    background:transparent;

    color:white;

    cursor:pointer;

    transition:0.2s;
}

.header-btn:hover{

    background:rgba(255,255,255,0.12);
}

.message-avatar-header{

    width:34px;
    height:34px;

    border-radius:50%;

    background:rgba(255,255,255,0.12);

    display:flex;
    align-items:center;
    justify-content:center;
}

.message-avatar-header i{

    font-size:15px;
    color:white;
}
/* ================= NEW MESSAGE PANEL ================= */

.new-message-panel{

    position:fixed;

    right:380px; /* sits left of messages panel */
    bottom:-500px;

    width:320px;
    height:450px;

    background:#1e293b;

    border-radius:15px 15px 0 0;

    transition:.4s;

    z-index:99998;

    overflow:hidden;

    color:white;
}

.new-message-panel.active{

    bottom:0;
}

.new-message-header{

    height:60px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    padding:0 15px;

    background:#0f172a;
}

.new-message-header h3{

    font-size:15px;
    font-weight:600;
}

.new-message-users{

    height:390px;

    overflow-y:auto;

    padding:8px;
}

.user-item{

    display:flex;
    align-items:center;

    gap:12px;

    padding:10px;

    border-radius:10px;

    cursor:pointer;

    transition:.2s;

    margin-bottom:5px;
}

.user-item:hover{

    background:rgba(255,255,255,.08);
}

.user-avatar{

    position:relative;

    width:40px;
    height:40px;

    border-radius:50%;

    background:rgba(255,255,255,.12);

    display:flex;
    align-items:center;
    justify-content:center;

    flex-shrink:0;

    overflow:visible;
}

.user-avatar i{

    font-size:18px;

    color:rgba(255,255,255,.85);
}


/*=====================================
USER ONLINE/OFFLINE STATUS DOT
=====================================*/

.user-status-dot{

    position:absolute;

    width:10px;
    height:10px;

    border-radius:50%;

    bottom:0;
    right:0;

    border:2px solid #181818;

    box-sizing:border-box;

    transition:.25s ease;
}

/* Online */

.user-status-dot.online{

    background:#2ecc71;

    box-shadow:
        0 0 6px rgba(46,204,113,.7);
}

/* Offline */

.user-status-dot.offline{

    background:#8d8d8d;
}

.user-name{

    font-size:13px;
    font-weight:600;
}
.chat-panel{

    position:fixed;

    right:720px;
    bottom:-500px;

    width:360px;
    height:450px;

    background:#1e293b;

    border-radius:15px 15px 0 0;

    overflow:hidden;

    transition:.4s;

    z-index:99997;
}

.chat-panel.active{

    bottom:0;
}

.chat-header{

    height:60px;

    background:#0f172a;

    display:flex;
    justify-content:space-between;
    align-items:center;

    padding:0 15px;

    color:white;
}

.chat-user{

    display:flex;
    align-items:center;

    gap:10px;
}

.chat-body{

    height:330px;

    overflow-y:auto;

    padding:10px;

    display:flex;
    flex-direction:column;

    gap:10px;
}

.chat-footer{

    height:60px;

    display:flex;

    border-top:1px solid rgba(255,255,255,.08);
}

.chat-footer input{

    flex:1;

    border:none;
    outline:none;

    background:transparent;

    color:white;

    padding:0 15px;
}

.chat-footer button{

    width:60px;

    border:none;

    background:#2563eb;

    color:white;

    cursor:pointer;
}

/* Logged-in user */

.my-message{

    align-self:flex-start;

    font-size:13px;

    max-width:75%;

    background:#2563eb;

    color:white;

    padding:10px 14px;

    border-radius:15px 15px 15px 5px;
}

/* Other user */

.their-message{

    align-self:flex-end;

    font-size:13px;

    max-width:75%;

    background:rgba(255,255,255,.12);

    color:white;

    padding:10px 14px;

    border-radius:15px 15px 5px 15px;
}
.no-transition{
    transition:none !important;
}
/* ================= ANNOUNCEMENTS ================= */

.announcements-panel{

    position:fixed;

    top:90px;
    right:20px;

    width:350px;
    max-height:450px;

    background:#1e293b;

    border-radius:18px;

    overflow:hidden;

    display:none;

    z-index:99999;

    box-shadow:0 15px 35px rgba(0,0,0,.35);
}

.announcements-header{

    height:60px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    padding:0 18px;

    background:#0f172a;

    color:white;
}

.announcements-header button{

    border:none;
    background:none;

    color:white;

    cursor:pointer;

    font-size:18px;
}

.announcements-list{

    padding:12px;

    max-height:390px;

    overflow-y:auto;
}

.announcement-item{

    padding:12px;

    border-radius:12px;

    background:rgba(255,255,255,.06);

    margin-bottom:10px;
}

.announcement-item strong{

    display:block;

    color:white;

    font-size:13px;

    margin-bottom:4px;
}

.announcement-item p{

    color:rgba(255,255,255,.75);

    font-size:12px;

    margin:0;
}
.announcement-actions{
    padding:12px;
    border-bottom:1px solid rgba(255,255,255,.08);
}

.add-announcement-btn{
    width:100%;
    padding:10px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:14px;
    font-weight:600;
}
/* PROFILE DROPDOWN */

.profile-dropdown{
    position:relative;
}

.profile{
    cursor:pointer;
}

.profile-arrow{
    font-size:12px;
    margin-left:8px;
}

/* MENU */

.profile-menu{

    position:absolute;

    top:60px;
    right:0;

    width:220px;

    background:#ffffff;

    border-radius:12px;

    overflow:hidden;

    box-shadow:
        0 10px 25px rgba(0,0,0,.25);

    display:none;

    z-index:99999;
}

.profile-menu.active{
    display:block;
}

.profile-menu a{

    display:flex;
    align-items:center;
    gap:10px;

    padding:12px 15px;

    color:#374151;

    text-decoration:none;

    font-size:13px;

    transition:.2s;
}

.profile-menu a:hover{
    background:#f3f4f6;
}

.profile-menu a i{
    width:18px;
}

.logout-item{
    color:#dc2626 !important;
}
.admin-item{

    background:
        linear-gradient(
            135deg,
            #059669,
            #10b981
        );

    color:white !important;

    font-weight:600;
}

.admin-item:hover{

    background:
        linear-gradient(
            135deg,
            #047857,
            #059669
        ) !important;

    color:white !important;
}

.admin-item i{
    color:white;
}
.admin-dashboard{
    width:100%;
}

.stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:15px;
    margin-bottom:20px;
}

.stat-card{
    background:rgba(255,255,255,.08);
    backdrop-filter:blur(15px);
    border-radius:20px;
    padding:20px;
    text-align:center;
    color:white;
}

.stat-card i{
    font-size:28px;
    margin-bottom:10px;
}

.stat-card h2{
    font-size:28px;
}

.stat-card p{
    opacity:.8;
}

.admin-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    margin-bottom:20px;
}

.admin-card{
    background:rgba(255,255,255,.08);
    backdrop-filter:blur(15px);
    border-radius:20px;
    padding:20px;
    color:white;
}

.admin-card h3{
    margin-bottom:15px;
}

.admin-buttons{
    display:flex;
    flex-direction:column;
    gap:10px;
}

.admin-buttons button{
    height:36px;
    border:none;
    border-radius:8px;
    background:linear-gradient(
        135deg,
        #2563eb,
        #ff3b5c
    );
    color:white;
    font-weight:400;
    cursor:pointer;
}

.activity-table{
    width:100%;
    border-collapse:collapse;
}

.activity-table th,
.activity-table td{
    padding:10px;
    border-bottom:1px solid rgba(255,255,255,.1);
    color:white;
}

@media(max-width:1000px){

    .admin-grid{
        grid-template-columns:1fr;
    }

}
.summary-table{
    width:100%;
    border-collapse:collapse;
    font-size:12px;
    background:rgba(255,255,255,.03);
}

.summary-table th{
    background:#2c3e50;
    color:#fff;
    font-weight:600;
    padding:8px 10px;
    text-align:left;
    border:1px solid rgba(255,255,255,.08);
}

.summary-table td{
    padding:7px 10px;
    color:white;
    border:1px solid rgba(255,255,255,.06);
}

.summary-table tbody tr:nth-child(even){
    background:rgba(255,255,255,.04);
}

.summary-table tbody tr:nth-child(odd){
    background:rgba(255,255,255,.02);
}

.summary-table tbody tr:hover{
    background:rgba(37,99,235,.18);
}
.mini-btn{
    border:none;
    background:transparent;
    color:#60a5fa;

    font-size:12px;
    cursor:pointer;

    padding:2px 4px;
    margin-right:8px;
}

.mini-btn:hover{
    color:#93c5fd;
}
.role-badge{
    display:inline-block;

    padding:2px 8px;

    border-radius:20px;

    font-size:11px;
    font-weight:600;

    background:#2563eb;
    color:white;
}


#diptankPopup tbody tr:hover{
    background:rgba(255,255,255,.03);
}

/* Icons */

.status-saving-icon,
.status-success-icon,
.status-error-icon{

    font-size:30px;

    margin-bottom:10px;
}

.status-saving-icon{
    color:#60a5fa;
}

.status-success-icon{
    color:#10b981;
}

.status-error-icon{
    color:#ef4444;
}

/* Title */

.status-title{

    font-size:14px;

    font-weight:600;

    color:white;

    margin-bottom:4px;
}

/* Message */

.status-message{

    font-size:12px;

    color:rgba(255,255,255,.70);

    margin-bottom:12px;

    line-height:1.4;
}

/* Buttons */

.status-btn{

    height:34px;

    min-width:80px;

    border:none;

    border-radius:8px;

    font-size:12px;

    font-weight:500;

    cursor:pointer;
}

.success-btn{

    background:#10b981;

    color:white;
}

.error-btn{

    background:#ef4444;

    color:white;
}

.success-btn:hover{
    background:#059669;
}

.error-btn:hover{
    background:#dc2626;
}
.large-popup{
    width: 90%;
    max-width: 1000px;
}




.notifications-popup{
    width:420px;
    max-width:95%;

    padding:0;

    border-radius:28px;

    background:
        linear-gradient(
            135deg,
            rgba(15,23,42,0.98),
            rgba(30,41,59,0.96)
        );

    border:1px solid rgba(255,255,255,0.08);

    overflow:hidden;

    color:white;
}



body{
    background:#121827;
    margin:0;
    font-family:Poppins,sans-serif;
}

.dashboard-layout{
    display:flex;
}

.sidebar{
    width:260px;
    height:100vh;
    background:#171f33;
    position:fixed;
    left:0;
    top:0;
}

.sidebar-logo{
    padding:25px;
    color:white;
    font-size:20px;
    font-weight:600;
}

.sidebar-menu{
    list-style:none;
    padding:0;
}

.sidebar-menu li{
    color:#cbd5e1;
    padding:15px 25px;
    cursor:pointer;
    transition:.3s;
}

.sidebar-menu li:hover{
    background:#23304d;
}

.sidebar-menu li.active{
    background:#2563eb;
    color:white;
}

.topbar{
    height:70px;
    background:#171f33;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 25px;

    position:sticky;
    top:0;
    z-index:1000;
}

.search-box{
    width:400px;
}

.search-box input{
    width:100%;
    background:#0f172a;
    border:none;
    color:white;
    padding:12px;
    border-radius:8px;
}
.dashboard-content{
    padding:15px;
    height:calc(100vh - 70px); /* viewport minus topbar */
    overflow-y:auto;
    overflow-x:hidden;
    box-sizing:border-box;
}
.dashboard-row{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:15px;
    margin-bottom:15px;
}
.dashboard-card{
    background:#1b2438;
    color:white;
    border-radius:12px;
    padding:12px;
    overflow:hidden;
}

.dashboard-card h4{
    margin:0 0 8px 0;
    font-size:13px;
    font-weight:600;
}

.stat-value{
    font-size:24px;
    font-weight:700;
}

.content-grid{
    display:grid;
    grid-template-columns:65% 35%;
    gap:15px;
    margin-top:15px;
    align-items:start;
}
.dashboard-table{
    max-height:180px;
    overflow-y:auto;
    display:block;
    font-size:12px;
}

.dashboard-table th,
.dashboard-table td{
    padding:8px;
}

.status-complete{
    color:#10b981;
}

.status-pending{
    color:#f59e0b;
}
.main-area{
    margin-left:260px;
    width:calc(100vw - 260px);
    min-height:100vh;
    overflow-x:hidden;
}

.chart-card{
    height:300px;
    position:relative;
    overflow:hidden;
}
.dashboard-card{
    overflow:hidden;
    box-sizing:border-box;
}
.chart-card canvas{
    flex:1;
    min-height:0;
}
.content-grid{
    display:grid;
    grid-template-columns:65% 35%;
    gap:15px;
    align-items:stretch;
}

.content-grid > .dashboard-card{
    height:320px; /* same height for both cards */
    box-sizing:border-box;
}
.topbar-right{
    display:flex;
    align-items:center;
    gap:15px;
}

.icon-box{
    width:40px;
    height:40px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#1b2438;
    border-radius:10px;
    color:white;
    cursor:pointer;
}

.profile{
    display:flex;
    align-items:center;
    gap:10px;
    color:white;
}

.profile-icon{
    width:38px;
    height:38px;
    border-radius:50%;
    background:#2563eb;
    display:flex;
    align-items:center;
    justify-content:center;
}
.dashboard-content{
    padding:25px;
    margin-top:0px;
}
.sidebar-logo{
    display:flex;
    align-items:center;
    gap:12px;
    padding:25px;
}

.sidebar-logo img{
    width:38px;
    height:38px;
    object-fit:contain;
}
@media(max-width:900px){

    .sidebar{
        width:80px;
    }

    .sidebar span{
        display:none;
    }

    .main-area{
        margin-left:80px;
        width:calc(100% - 80px);
    }

    .topbar{
        width:calc(100% - 80px);
    }

    .content-grid{
        grid-template-columns:1fr;
    }
}

@media(max-width:700px){

    .search-box{
        display:none;
    }

    .dashboard-content{
        padding:15px;
    }

    .dashboard-row{
        grid-template-columns:1fr;
    }
}
.reports-card{
    max-height:250px;
    overflow-y:auto;
}
.activity-card{
    max-height:250px;
    overflow-y:auto;
}
.dashboard-content::-webkit-scrollbar{
    width:8px;
}

.dashboard-content::-webkit-scrollbar-track{
    background:#0f172a;
}

.dashboard-content::-webkit-scrollbar-thumb{
    background:#2563eb;
    border-radius:20px;
}
.popup-toolbar{
    display:flex;
    justify-content:flex-end;
    margin-bottom:15px;
}

.popup-toolbar .save-btn{
    height:40px;
    padding:0 18px;
    border:none;
    border-radius:10px;
    background:#2563eb;
    color:white;
    cursor:pointer;
    font-size:13px;
    font-weight:600;
}




/* =========================
   DASHBOARD TABLE STYLING
   ========================= */

.dashboard-card {
    background: rgba(255,255,255,0.04);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
}

/* Card Titles */
.dashboard-card h4 {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #fff;
    margin-bottom: 15px;
}

/* TABLE BASE */
.dashboard-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    color: #e5e7eb;
}

/* HEADER */
.dashboard-table thead tr {
    background: rgba(255,255,255,0.06);
    text-align: left;
}

.dashboard-table thead th {
    padding: 12px 10px;
    font-weight: 600;
    color: #cbd5e1;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

/* ROWS */
.dashboard-table tbody tr {
    border-bottom: 1px solid rgba(255,255,255,0.06);
    transition: 0.2s ease;
}

.dashboard-table tbody tr:hover {
    background: rgba(255,255,255,0.05);
    transform: scale(1.01);
}

/* CELLS */
.dashboard-table td {
    padding: 12px 10px;
    vertical-align: middle;
}

/* USER ICON ALIGNMENT */
.dashboard-table td i {
    margin-right: 6px;
    color: #93c5fd;
}

/* =========================
   STATUS STYLES
   ========================= */

.status-active {
    color: #22c55e;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-inactive {
    color: #ef4444;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* TIME LABELS */
.time-active {
    color: #22c55e;
    font-size: 12px;
}

.time-inactive {
    color: #ef4444;
    font-size: 12px;
}

/* ICONS */
.status-icon {
    font-size: 10px;
}

/* =========================
   GLOBAL SCROLLBAR STYLING
   ========================= */

/* Width */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

/* Track (background) */
::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
}

/* Thumb (scroll indicator) */
::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #3b82f6, #ef4444);
    border-radius: 10px;
    transition: 0.3s;
}

/* Hover effect */
::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #60a5fa, #f87171);
}

/* =========================
   TABLE SCROLL AREA
   ========================= */

.table-responsive,
.dashboard-table-wrapper {
    max-height: 320px;
    overflow-y: auto;
    overflow-x: auto;
    border-radius: 12px;
}

/* Optional: sticky header inside scroll */
.dashboard-table thead th {
    position: sticky;
    top: 0;
    background: rgba(20, 25, 40, 0.95);
    backdrop-filter: blur(10px);
    z-index: 2;
}

/* =========================
   SIDEBAR SCROLL (if needed)
   ========================= */

.sidebar {
    overflow-y: auto;
}

/* thin elegant sidebar scrollbar */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.35);
}
.chart-wrapper{
    width:100%;
    background:#0f172a;
    padding:14px;
    border-radius:12px;
    color:#fff;
}

.chart-container{
    display:flex;
    align-items:stretch;
    gap:4px;
}

.y-axis{
    width:40px;
    height:200px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    text-align:right;
    color:#9ca3af;
    font-size:11px;
}

.graph-area{
    flex:1;
    min-width:0;
}

.graph{
    position:relative;
    width:100%;
    height:200px;
    background:#111827;
    border-radius:12px;
    overflow:hidden;
}

.grid{
    position:absolute;
    inset:0;
    background-image:
        linear-gradient(#1f2937 1px, transparent 1px);
    background-size:100% 20%;
    opacity:.35;
}

.line-svg{
    position:absolute;
    inset:0;
    width:100%;
    height:100%;
}

.line-svg polyline{
    fill:none;
    stroke-width:1.2;
    stroke-linecap:round;
    stroke-linejoin:round;
    vector-effect:non-scaling-stroke;
}

.x-axis{
    display:flex;
    justify-content:space-between;
    margin-top:8px;
    color:#9ca3af;
    font-size:10px;
}

.x-axis-label{
    text-align:center;
    margin-top:6px;
    color:#9ca3af;
    font-size:12px;
    font-weight:600;
    letter-spacing:0.5px;
}
.y-axis-label{
    width:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    writing-mode:vertical-rl;
    transform:rotate(180deg);
    color:#9ca3af;
    font-size:11px;
    font-weight:600;
}

.y-axis{
    width:25px;
    height:200px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    align-items:flex-end;
    font-size:11px;
    color:#9ca3af;
}
.line-svg polyline{
    fill:none;
    stroke-width:2;
    stroke-linecap:round;
    stroke-linejoin:round;
    vector-effect:non-scaling-stroke;
}

.permit-line{
    stroke:#facc15;
}

.permit-out-line{
    stroke:#3b82f6;
}

.birth-line{
    stroke:#22c55e;
}

.death-line{
    stroke:#ef4444;
}

.chart-legend{
    display:flex;
    flex-wrap:wrap;
    gap:20px;
    margin-top:15px;
    color:#d1d5db;
    font-size:12px;
}

.chart-legend span{
    display:flex;
    align-items:center;
    gap:6px;
}

.legend-permit,
.legend-permit-out,
.legend-birth,
.legend-death{
    width:20px;
    height:3px;
    border-radius:2px;
    display:inline-block;
}

.legend-permit{
    background:#facc15;
}

.legend-permit-out{
    background:#3b82f6;
}

.legend-birth{
    background:#22c55e;
}

.legend-death{
    background:#ef4444;
}


/* =========================
   3D BUTTON BASE
========================= */
.btn-3d{
    border:none;
    cursor:pointer;
    padding:5px 10px;      /* reduced height */
    border-radius:8px;
    font-size:12px;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:5px;
    line-height:1;
    transition:all .2s ease;
    box-shadow:
        0 3px 0 rgba(0,0,0,.25),
        0 5px 10px rgba(0,0,0,.25);
}

/* PRESS EFFECT */
.btn-3d:active {
    transform: translateY(3px);
    box-shadow: 0 1px 0 rgba(0,0,0,0.25);
}

/* =========================
   ADD BUTTON (SKY BLUE)
========================= */
.users-toolbar{
    display:flex;
    justify-content:flex-end;
    gap:5px;
    margin-bottom:10px;
}

.users-toolbar input{
    width:220px;
    height:30px;
    border:1px solid #46516a;
    border-radius:3px;
    background:#20293c;
    color:#fff;
    padding:0 10px;
    outline:none;
}

.toolbar-btn{
    width:32px;
    height:30px;
    border:1px solid #46516a;
    background:#2b364d;
    color:#fff;
    cursor:pointer;
    border-radius:3px;
}

.users-table-container{
    border:1px solid #39465d;
    border-radius:6px;
    overflow:hidden;
}

.users-table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

.users-table thead th{
    background:#334155;
    color:#f8fafc;
    font-size:12px;
    font-weight:600;
    padding:8px;
    text-align:left;
    border-right:1px solid #475569;
}

.users-table tbody td{
    padding:8px;
    font-size:13px;
    color:#e2e8f0;
    border-top:1px solid #374151;
    border-right:1px solid #374151;
}

.users-table tbody tr{
    background:#1f2937;
}

.users-table tbody tr:nth-child(even){
    background:#243042;
}

.users-table tbody tr:hover{
    background:#2d3a4f;
}

.role-badge{
    display:inline-block;
    padding:3px 10px;
    border-radius:20px;
    background:#2563eb;
    color:#fff;
    font-size:11px;
    font-weight:600;
}

.action-btn{
    width:28px;
    height:28px;
    border:none;
    background:transparent;
    color:#60a5fa;
    cursor:pointer;
    transition:.2s;
}

.action-btn:hover{
    transform:scale(1.1);
}

.edit-btn{
    color:#60a5fa;
}

.permission-btn{
    color:#93c5fd;
}

/* Close button */
.close-btn{
    width:32px;
    height:32px;
    border-radius:5px;
    color:#fff;
    background:linear-gradient(
        to bottom,
        #ef4444,
        #dc2626
    );
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.2),
        0 2px 4px rgba(0,0,0,.25);
}

.close-btn:hover{
    transform:translateY(-1px);
    background:linear-gradient(
        to bottom,
        #f87171,
        #ef4444
    );
}

.door-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;

    padding: 12px 18px;
    border: none;
    border-radius: 10px;

    background: linear-gradient(135deg, #1f2937, #0ea5e9); /* dark grey → sky blue */
    color: #ffffff;

    font-size: 15px;
    font-weight: 600;
    font-family: Arial, sans-serif;

    cursor: pointer;

    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
    transition: all 0.25s ease-in-out;

    letter-spacing: 0.3px;
}

.door-btn i {
    font-size: 18px;
    color: #ffffff;
}

.door-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.35);
    background: linear-gradient(135deg, #0f172a, #38bdf8);
}

.door-btn:active {
    transform: scale(0.97);
}

/* HEADER WITH EXPAND BUTTON */
.chart-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.expand-btn{
    background:#1d74e8;
    border:none;
    color:#fff;
    width:28px;
    height:28px;
    border-radius:6px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:12px;
    position:relative;
    top:-10px;   /* move slightly up */
}

/* POPUP BACKDROP */
.chart-popup{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,.6);
    z-index:9999;
    align-items:center;
    justify-content:center;
}

/* POPUP BOX */
.chart-popup-content{
    width:85%;
    height:80%;
    background:#2b313c;
    border-radius:12px;
    display:flex;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,.5);
}

/* LEFT CHART */
.chart-popup-main{
    flex:2;
    padding:15px;
    background:#1f2430;
}

/* RIGHT STATS */
.chart-popup-stats{
    flex:1;
    padding:15px;
    background:#2d333e;
    color:#fff;
}

.stat-box{
    background:#3a4250;
    padding:10px;
    margin-top:10px;
    border-radius:8px;
}

.close-popup-btn{
    margin-top:15px;
    width:100%;
    padding:8px;
    background:#e74c3c;
    border:none;
    color:#fff;
    border-radius:6px;
    cursor:pointer;
}

/* =========================================================
   EXPAND ICON (WHITE ONLY)
========================================================= */
.expand-btn{
    background:none;
    border:1px solid rgba(255,255,255,0.25);
    color:#ffffff;

    width:30px;
    height:30px;

    border-radius:8px;

    display:flex;
    align-items:center;
    justify-content:center;

    cursor:pointer;

    transition:0.25s ease;

    /* soft glow */
    box-shadow:
        0 0 6px rgba(255,255,255,0.15),
        inset 0 0 6px rgba(255,255,255,0.05);
}

/* icon itself */
.expand-btn i{
    font-size:13px;
    color:#ffffff;
}

/* hover effect */
.expand-btn:hover{
    border-color:rgba(255,255,255,0.7);

    box-shadow:
        0 0 12px rgba(255,255,255,0.45),
        0 0 20px rgba(120,180,255,0.25),
        inset 0 0 8px rgba(255,255,255,0.08);

    transform:scale(1.08);
}

/* click effect */
.expand-btn:active{
    transform:scale(0.96);
    box-shadow:
        0 0 6px rgba(255,255,255,0.25);
}

/* =========================================================
   POPUP BACKDROP
========================================================= */
.win-popup{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
    backdrop-filter:blur(8px);
    z-index:9999;
    align-items:center;
    justify-content:center;
}

/* =========================================================
   WINDOW CONTAINER (WINDOWS 11 STYLE)
========================================================= */
.win-window{
    width:90%;
    height:90%;
    background:#1f232b;
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 25px 60px rgba(0,0,0,0.6);
    display:flex;
    flex-direction:column;
}

/* =========================================================
   TITLE BAR
========================================================= */
.win-titlebar{
    display:flex;
    justify-content:space-between;
    align-items:center;

    padding:10px 14px;

    background:linear-gradient(
        90deg,
        #2b313c,
        #1f2430
    );

    border-bottom:1px solid rgba(255,255,255,0.08);
}

.win-title{
    display:flex;
    align-items:center;
    gap:8px;
    color:#fff;
    font-size:13px;
    font-weight:600;
}

.win-controls button{
    background:none;
    border:none;
    color:#fff;
    cursor:pointer;
    font-size:14px;
    padding:6px;
    border-radius:6px;
    transition:0.2s;
}

.win-controls button:hover{
    background:#e81123;
}

/* =========================================================
   BODY
========================================================= */
.win-body{
    flex:1;
    display:flex;
    overflow:hidden;
}

/* LEFT CHART */
.win-chart{
    flex:2;
    padding:14px;
    background:#1b1f27;
    overflow:auto;
}

/* RIGHT STATS */
.win-stats{
    flex:1;
    padding:14px;
    background:#242a33;
    border-left:1px solid rgba(255,255,255,0.08);
    color:#fff;
}

.win-stats h3{
    margin-bottom:10px;
    font-size:14px;
}

.stat-box{
    background:#2f3642;
    padding:10px;
    border-radius:10px;
    margin-bottom:10px;
}

.chart-toggles{
    display:flex;
    gap:8px;
    margin-top:10px;
    flex-wrap:wrap;
}

.chart-toggle{
    padding:5px 10px;
    border-radius:20px;

    border:1px solid rgba(255,255,255,0.2);
    background:transparent;

    color:#fff;
    font-size:11px;

    cursor:pointer;

    transition:0.2s;
}

.chart-toggle:hover{
    border-color:#7ec8ff;
    box-shadow:0 0 8px rgba(126,200,255,0.4);
}

.chart-toggle.active{
    background:#1d74e8;
    border-color:#1d74e8;
}

.permit-out-toggle{
    border:1px solid rgba(255,255,255,0.25);
    background:transparent;
    color:#fff;

    padding:5px 10px;
    border-radius:20px;

    font-size:11px;
    cursor:pointer;

    transition:0.2s;
}

.permit-out-toggle.active{
    background:#ff6666;
    border-color:#ff6666;
    box-shadow:0 0 10px rgba(255,102,102,0.4);
}

.permit-out-toggle:hover{
    border-color:#ff9999;
}

.chart-toggles{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;
    margin-top:12px;
}

.chart-toggle-label{
    display:flex;
    align-items:center;
    gap:6px;

    margin-right:8px;

    color:#d8dde6;
    font-size:12px;
    font-weight:600;

    white-space:nowrap;
}

.chart-toggle-label i{
    color:#4da3ff;
    font-size:12px;
}

 .chart-settings{

    margin-top:18px;

    padding-top:12px;

    border-top:1px solid rgba(255,255,255,.08);

}

.chart-setting-title{

    display:flex;

    align-items:center;

    gap:8px;

    color:#d7dde7;

    font-size:12px;

    font-weight:600;

    margin-bottom:10px;

}

.chart-setting-title i{

    color:#4da3ff;

}

.axis-options{

    display:flex;

    flex-wrap:wrap;

    gap:8px;

}

.axis-btn{

    padding:6px 12px;

    border:none;

    border-radius:20px;

    background:#303846;

    color:#fff;

    font-size:11px;

    cursor:pointer;

    transition:.2s;

}

.axis-btn:hover{

    background:#3f4d62;

}

.axis-btn.active{

    background:#2474ff;

    box-shadow:0 0 10px rgba(36,116,255,.45);

}

.grid {
    stroke: rgba(255,255,255,0.08);
}

.dot {
    fill: #ffffff;
    opacity: 0.9;
    transition: 0.2s;
}

.dot:hover {
    transform: scale(1.4);
    opacity: 1;
}

/* ==========================================
   CHART DATA POINTS
========================================== */

/* Common style for all dots */
.dot{
    stroke:#ffffff;
    transition:all .2s ease;
    cursor:pointer;
}

/* Hover effect */
.dot:hover{
    r:2.8;
    filter:drop-shadow(0 0 5px rgba(255,255,255,.6));
}

/* ==========================================
   PERMITS IN (Yellow)
========================================== */
.permit-dot{
    fill:#f4c542;
}

/* ==========================================
   PERMITS OUT (Blue)
========================================== */
.permit-out-dot{
    fill:#3b82f6;
}

/* ==========================================
   BIRTHS (Green)
========================================== */
.birth-dot{
    fill:#22c55e;
}

/* ==========================================
   DEATHS (Red)
========================================== */
.death-dot{
    fill:#ef4444;
}

.v-grid-line{
    stroke: rgba(255,255,255,0.08);
    stroke-width: 0.3;
    shape-rendering: crispEdges;
}

.line-svg circle{
    vector-effect: non-scaling-stroke;
}

/* ==========================
   OVERLAY
========================== */

.popup-overlay{
    position:fixed;
    inset:0;
    display:none;
    justify-content:center;
    align-items:center;

    background:rgba(0,0,0,.55);
    backdrop-filter:blur(6px);

    z-index:99999;
}

/* ==========================
   WINDOWS 11 STYLE POPUP
========================== */

.popup-box.fixed-popup{
    width:430px;
    max-width:95vw;
    height:520px;

    display:flex;
    flex-direction:column;

    background:linear-gradient(
        180deg,
        #1f2937 0%,
        #172030 100%
    );

    border:1px solid rgba(255,255,255,.08);

    border-radius:12px;

    overflow:hidden;

    box-shadow:
        0 18px 40px rgba(0,0,0,.45),
        inset 0 1px 0 rgba(255,255,255,.06);

    animation:win11Popup .2s ease;
}

/* ==========================
   HEADER
========================== */

.popup-header{
    height:44px;

    flex-shrink:0;

    display:flex;
    align-items:center;
    justify-content:space-between;

    padding:0 14px;

    background:linear-gradient(
        to bottom,
        #394252,
        #2d3645
    );

    border-bottom:1px solid rgba(255,255,255,.08);
}

.popup-header h2{
    margin:0;

    display:flex;
    align-items:center;
    gap:8px;

    font-size:13px;
    font-weight:600;

    color:#ffffff;
}


/* ==========================
   FORM
========================== */

#diptankForm{
    flex:1;

    display:flex;
    flex-direction:column;

    overflow:hidden;
}

/* ==========================
   SCROLLABLE CONTENT
========================== */

.popup-body{
    flex:1;

    overflow-y:auto;

    padding:14px;
}

/* Thin scrollbar */

.popup-body::-webkit-scrollbar{
    width:6px;
}

.popup-body::-webkit-scrollbar-thumb{
    background:#4b5563;
    border-radius:20px;
}

.popup-body::-webkit-scrollbar-track{
    background:transparent;
}

/* ==========================
   FORM GROUPS
========================== */

.form-group{
    margin-bottom:10px;
}

.form-group label{
    display:block;

    margin-bottom:4px;

    font-size:12px;
    font-weight:600;

    color:#d1d5db;
}

.form-group input,
.form-group select{
    width:100%;
    height:32px;

    padding:0 10px;

    border-radius:6px;

    border:1px solid #4b5563;

    background:#374151;

    color:#ffffff;

    font-size:12px;

    outline:none;

    box-sizing:border-box;

    transition:.2s;
}

.form-group input:focus,
.form-group select:focus{
    border-color:#60a5fa;

    box-shadow:
        0 0 0 2px rgba(96,165,250,.15);
}

/* ==========================
   FIXED FOOTER
========================== */

.form-actions{
    flex-shrink:0;

    display:flex;
    justify-content:flex-end;
    gap:8px;

    padding:10px 14px;

    background:linear-gradient(
        to bottom,
        #2d3645,
        #232c39
    );

    border-top:1px solid rgba(255,255,255,.08);
}

/* ==========================
   BUTTONS
========================== */

.cancel-btn,
.save-btn{
    height:32px;

    border:none;

    border-radius:6px;

    padding:0 14px;

    font-size:12px;
    font-weight:600;

    cursor:pointer;

    display:flex;
    align-items:center;
    gap:6px;

    transition:.2s;
}

.cancel-btn{
    color:#fff;

    background:linear-gradient(
        to bottom,
        #6b7280,
        #4b5563
    );

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.15),
        0 2px 5px rgba(0,0,0,.25);
}

.save-btn{
    color:#fff;

    background:linear-gradient(
        to bottom,
        #3b82f6,
        #2563eb
    );

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.15),
        0 3px 8px rgba(37,99,235,.35);
}

.cancel-btn:hover,
.save-btn:hover{
    transform:translateY(-1px);
}

.cancel-btn:active,
.save-btn:active{
    transform:translateY(1px);
}

/* ==========================
   ANIMATION
========================== */

@keyframes win11Popup{

    from{
        opacity:0;
        transform:scale(.97) translateY(-8px);
    }

    to{
        opacity:1;
        transform:scale(1) translateY(0);
    }

}

#editUserPopup .popup-box{
    width:440px;
    height:500px;
}

#editUserPopup .fixed-popup{
    width:440px;
    max-width:95vw;

    height:520px;

    display:flex;
    flex-direction:column;
}

/* =========================
   USERS POPUP
========================= */

.win11-users-popup{
    width:850px;
    max-width:95vw;
    height:520px;

    display:flex;
    flex-direction:column;

    background:linear-gradient(
        180deg,
        #1f2937,
        #172030
    );

    border:1px solid rgba(255,255,255,.08);

    border-radius:12px;

    overflow:hidden;

    box-shadow:
        0 18px 40px rgba(0,0,0,.45),
        inset 0 1px 0 rgba(255,255,255,.05);
}

/* =========================
   HEADER
========================= */

.win11-users-popup .popup-header{
    height:42px;

    flex-shrink:0;

    padding:0 14px;

    background:linear-gradient(
        to bottom,
        #394252,
        #2d3645
    );

    border-bottom:1px solid rgba(255,255,255,.08);
}

.win11-users-popup .popup-header h2{
    font-size:13px;
    font-weight:600;
}

.popup-close-btn{
    width:28px;
    height:28px;

    border:none;
    border-radius:6px;

    background:rgba(255,255,255,.05);

    color:#d5d9df;

    cursor:pointer;
}

.popup-close-btn:hover{
    background:#dc2626;
    color:#fff;
}

/* =========================
   TOOLBAR
========================= */

.users-toolbar{
    flex-shrink:0;

    display:flex;
    gap:8px;

    padding:10px 12px;

    border-bottom:1px solid rgba(255,255,255,.05);
}

.users-toolbar input{
    flex:1;

    height:30px;

    border:1px solid #4b5563;
    border-radius:6px;

    background:#374151;

    color:#fff;

    padding:0 10px;

    font-size:12px;
}

.toolbar-btn{
    width:32px;
    height:30px;

    border:none;
    border-radius:6px;

    background:#2563eb;

    color:#fff;

    cursor:pointer;
}

/* =========================
   TABLE AREA
========================= */

.users-table-container{
    flex:1;

    overflow:auto;

    padding:10px;
}

/* =========================
   TABLE
========================= */

.users-table{
    width:100%;
    border-collapse:collapse;

    font-size:12px;
}

.users-table thead th{
    position:sticky;
    top:0;

    background:#2d3645;

    color:#fff;

    padding:8px;

    text-align:left;

    font-size:12px;

    border-bottom:1px solid #4b5563;
}

.users-table tbody td{
    padding:7px 8px;

    border-bottom:1px solid rgba(255,255,255,.05);

    color:#d1d5db;
}

.users-table tbody tr:hover{
    background:rgba(255,255,255,.04);
}

/* =========================
   ROLE BADGES
========================= */

.role-badge{
    display:inline-block;

    padding:3px 8px;

    border-radius:20px;

    background:#334155;

    color:#fff;

    font-size:11px;
}

/* =========================
   ACTION BUTTONS
========================= */

.action-btn{
    width:28px;
    height:28px;

    border:none;
    border-radius:6px;

    cursor:pointer;

    margin-right:4px;
}

.edit-btn{
    background:#2563eb;
    color:#fff;
}

.key-btn{
    background:#f59e0b;
    color:#fff;
}

/* =========================
   FOOTER
========================= */

.users-footer{
    flex-shrink:0;

    height:48px;

    display:flex;
    align-items:center;
    justify-content:space-between;

    padding:0 12px;

    background:linear-gradient(
        to bottom,
        #2d3645,
        #232c39
    );

    border-top:1px solid rgba(255,255,255,.08);
}

.users-footer span{
    color:#cbd5e1;
    font-size:12px;
    font-weight:500;
}

.diptank-popup{
    width:900px;
    max-width:95vw;
    height:560px;

    display:flex;
    flex-direction:column;

    border-radius:12px;
    overflow:hidden;
}

.table-badge{
    display:inline-block;

    min-width:70px;

    text-align:center;

    padding:3px 8px;

    border-radius:20px;

    background:#334155;

    color:#fff;

    font-size:11px;
    font-weight:600;
}

.popup-header-actions{
    display:flex;
    align-items:center;
    gap:8px;
}

/* Primary Action Button */
.create-diptank-btn{
    height:30px;

    display:flex;
    align-items:center;
    gap:6px;

    padding:0 12px;

    border:none;
    border-radius:7px;

    background:linear-gradient(
        to bottom,
        #4da3ff,
        #2563eb
    );

    color:#fff;

    font-size:12px;
    font-weight:600;

    cursor:pointer;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.18),
        0 2px 6px rgba(37,99,235,.35);

    transition:all .18s ease;
}

.create-diptank-btn:hover{
    transform:translateY(-1px);

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.2),
        0 4px 10px rgba(37,99,235,.45);
}

.create-diptank-btn:active{
    transform:translateY(1px);

    box-shadow:
        inset 0 2px 4px rgba(0,0,0,.2);
}

.create-diptank-btn i{
    font-size:11px;
}

/* Close Button */
.popup-close-btn{
    width:30px;
    height:30px;

    display:flex;
    align-items:center;
    justify-content:center;

    border:none;
    border-radius:7px;

    background:rgba(255,255,255,.06);

    color:#d5d9df;

    cursor:pointer;

    transition:.18s ease;
}

.popup-close-btn:hover{
    background:#dc2626;
    color:#fff;
}

.win-close-btn{
    width:30px;
    height:30px;

    display:flex;
    align-items:center;
    justify-content:center;

    border:none;
    border-radius:6px;

    background:transparent;

    color:#d1d5db;

    cursor:pointer;

    transition:all .15s ease;
}

.win-close-btn:hover{
    background:#e81123;
    color:#fff;
}

.win-close-btn:active{
    background:#c50f1f;
}

.win-primary-btn{
    height:30px;

    display:flex;
    align-items:center;
    gap:6px;

    padding:0 12px;

    border:1px solid #4f8cff;

    border-radius:6px;

    background:linear-gradient(
        to bottom,
        #4f8cff,
        #2563eb
    );

    color:#fff;

    font-size:12px;
    font-weight:600;

    cursor:pointer;

    transition:all .15s ease;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.15),
        0 2px 6px rgba(37,99,235,.25);
}

.win-primary-btn:hover{
    background:linear-gradient(
        to bottom,
        #60a5fa,
        #3b82f6
    );

    transform:translateY(-1px);
}

.win-primary-btn:active{
    transform:translateY(1px);

    box-shadow:
        inset 0 2px 4px rgba(0,0,0,.2);
}

.win-primary-btn i{
    font-size:11px;
}

#editUserForm{
    flex:1;
    display:flex;
    flex-direction:column;
    overflow:hidden;
}
.region-tabs-container{
    width:35%;
    height:100%;

    display:flex;
    flex-direction:column;

    background:#1f2937;

    border:1px solid rgba(255,255,255,.08);
    border-radius:12px;

    overflow:hidden;

    box-shadow:
        0 8px 20px rgba(0,0,0,.25),
        inset 0 1px 0 rgba(255,255,255,.04);
}

/* Tabs Bar */

.region-tabs-header{
    display:flex;
    align-items:center;

    padding:8px;

    gap:6px;

    background:linear-gradient(
        to bottom,
        #374151,
        #2d3645
    );

    border-bottom:1px solid rgba(255,255,255,.08);
}

/* Individual Tabs */

.region-tab{
    height:34px;

    padding:0 18px;

    border:none;
    border-radius:8px;

    background:transparent;

    color:#cbd5e1;

    font-size:12px;
    font-weight:600;

    cursor:pointer;

    transition:.2s;
}

.region-tab:hover{
    background:rgba(255,255,255,.06);
}

.region-tab.active{
    background:linear-gradient(
        to bottom,
        #4f8cff,
        #2563eb
    );

    color:#fff;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.15),
        0 2px 6px rgba(37,99,235,.25);
}

/* Content Area */

.region-content{
    flex:1;

    overflow:auto;

    padding:15px;

    background:#111827;
}

/* Hidden Panes */

.region-pane{
    display:none;
}

.active-pane{
    display:block;
}

.chat-user{

    display:flex;
    align-items:center;
    gap:12px;
}

.chat-user-details{

    display:flex;
    flex-direction:column;
}

#chatUserName{

    font-weight:600;
    color:#fff;
}

.chat-user-presence{

    margin-top:2px;

    font-size:11px;

    color:#9e9e9e;
}

.chat-user-presence.online{

    color:#2ecc71;
}

.chat-user-presence.offline{

    color:#9e9e9e;
}

/*====================================================
    WINDOWS 11 POPUP
====================================================*/

.eartag-popup{

    position:fixed;

    inset:0;

    background:rgba(0,0,0,.65);

    backdrop-filter:blur(6px);

    display:none;

    justify-content:center;

    align-items:center;

    z-index:999999;

}

.eartag-popup.show{

    display:flex;

}

.eartag-popup-box{

    width:92%;

    max-width:1400px;

    height:88vh;

    background:
        linear-gradient(
            180deg,
            #1b1b1b,
            #090909
        );

    border-radius:12px;

    border:1px solid #333;

    overflow:hidden;

    box-shadow:
        0 30px 60px rgba(0,0,0,.65),
        inset 0 1px 0 rgba(255,255,255,.05);

    animation:eartagPopup .25s ease;

}

@keyframes eartagPopup{

    from{

        opacity:0;

        transform:scale(.94);

    }

    to{

        opacity:1;

        transform:scale(1);

    }

}

.open-eartag-btn{

    padding:10px 20px;

    border:none;

    border-radius:8px;

    cursor:pointer;

    font-size:13px;

    font-weight:600;

    color:white;

    background:
        linear-gradient(
            180deg,
            #2563eb,
            #1d4ed8
        );

    transition:.25s;

}

.open-eartag-btn:hover{

    transform:translateY(-2px);

    box-shadow:0 10px 20px rgba(37,99,235,.35);

}

.popup-close-btn{

    width:34px;

    height:34px;

    border:none;

    border-radius:8px;

    cursor:pointer;

    color:white;

    background:#202020;

    transition:.2s;

}

.popup-close-btn:hover{

    background:#ef4444;

}

/*=========================================================
    EARTAG MANAGEMENT
    WINDOWS 11 + PHPMYADMIN
    GLOSSY BLACK THEME
=========================================================*/

#eartagManagementSection{

    padding:12px;
    height:100%;
    overflow:hidden;

}

/*=========================================================
    MAIN CARD
=========================================================*/

#eartagManagementSection .dashboard-section{

    background:
        linear-gradient(
            180deg,
            #1d1d1d 0%,
            #0c0c0c 100%
        );

    border:1px solid #2f2f2f;

    border-radius:12px;

    overflow:hidden;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.05),
        0 10px 25px rgba(0,0,0,.45);

}

/*=========================================================
    HEADER
=========================================================*/

#eartagManagementSection .section-header{

    height:48px;

    display:flex;

    align-items:center;

    justify-content:space-between;

    padding:0 16px;

    background:
        linear-gradient(
            180deg,
            #262626,
            #111111
        );

    border-bottom:1px solid #2d2d2d;

}

#eartagManagementSection .section-header h2{

    margin:0;

    display:flex;

    align-items:center;

    gap:8px;

    color:#fff;

    font-size:17px;

    font-weight:600;

}

#eartagManagementSection .section-header h2 i{

    color:#38bdf8;

    font-size:16px;

}

/*=========================================================
    WINDOWS 11 TABS
=========================================================*/

.eartag-tabs{

    display:flex;

    gap:8px;

    padding:10px;

    background:#161616;

    border-bottom:1px solid #2d2d2d;

}

.eartag-tab{

    border:none;

    outline:none;

    cursor:pointer;

    padding:7px 18px;

    font-size:13px;

    font-weight:600;

    color:#d8d8d8;

    border-radius:8px;

    background:
        linear-gradient(
            180deg,
            #2d2d2d,
            #1b1b1b
        );

    border:1px solid #353535;

    transition:.25s;

}

.eartag-tab:hover{

    color:#fff;

    border-color:#3b82f6;

    box-shadow:
        0 0 8px rgba(59,130,246,.35);

}

.eartag-tab.active{

    color:#fff;

    background:
        linear-gradient(
            180deg,
            #2563eb,
            #1e40af
        );

    border-color:#60a5fa;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.18),
        0 0 12px rgba(37,99,235,.45);

}

/*=========================================================
    TAB CONTENT
=========================================================*/

.eartag-content{

    display:none;

    padding:10px;

}

.eartag-content.active-tab{

    display:block;

}

/*=========================================================
    TABLE
=========================================================*/

.records-table-wrapper{

    border-radius:10px;

    overflow:hidden;

    border:1px solid #2e2e2e;

    background:#111;

}

.table-container{

    max-height:500px;

    overflow:auto;

}

.records-table{

    width:100%;

    border-collapse:collapse;

    font-size:12px;

}

.records-table thead th{

    position:sticky;

    top:0;

    z-index:5;

    padding:10px;

    background:
        linear-gradient(
            180deg,
            #262626,
            #181818
        );

    color:#ffffff;

    font-weight:600;

    white-space:nowrap;

    border-bottom:1px solid #383838;

}

.records-table tbody td{

    padding:9px;

    color:#e8e8e8;

    border-bottom:1px solid #252525;

    white-space:nowrap;

}

.records-table tbody tr{

    transition:.2s;

}

.records-table tbody tr:nth-child(even){

    background:#151515;

}

.records-table tbody tr:hover{

    background:#1d3557;

}

.records-table tbody tr:hover td{

    color:#fff;

}

/*=========================================================
    INPUT
=========================================================*/

.eartag-input{

    width:120px;

    padding:6px 8px;

    border-radius:6px;

    border:1px solid #404040;

    background:#0f0f0f;

    color:#fff;

    font-size:12px;

    transition:.2s;

}

.eartag-input:focus{

    outline:none;

    border-color:#3b82f6;

    box-shadow:
        0 0 8px rgba(59,130,246,.35);

}

/*=========================================================
    BUTTONS
=========================================================*/

.approve-btn,
.reject-btn{

    border:none;

    cursor:pointer;

    padding:6px 12px;

    border-radius:6px;

    font-size:11px;

    font-weight:600;

    transition:.25s;

}

.approve-btn{

    background:
        linear-gradient(
            180deg,
            #22c55e,
            #15803d
        );

    color:#fff;

    margin-right:5px;

}

.approve-btn:hover{

    transform:translateY(-1px);

    box-shadow:
        0 4px 10px rgba(34,197,94,.35);

}

.reject-btn{

    background:
        linear-gradient(
            180deg,
            #ef4444,
            #991b1b
        );

    color:#fff;

}

.reject-btn:hover{

    transform:translateY(-1px);

    box-shadow:
        0 4px 10px rgba(239,68,68,.35);

}

/*=========================================================
    NO DATA
=========================================================*/

.no-data{

    text-align:center;

    color:#bdbdbd;

    padding:35px;

    font-size:13px;

}

/*=========================================================
    SCROLLBAR
=========================================================*/

.table-container::-webkit-scrollbar{

    width:8px;

    height:8px;

}

.table-container::-webkit-scrollbar-track{

    background:#111;

}

.table-container::-webkit-scrollbar-thumb{

    background:#3d3d3d;

    border-radius:10px;

}

.table-container::-webkit-scrollbar-thumb:hover{

    background:#60a5fa;

}

/*==================================================
 WINDOWS 11 HEADER
==================================================*/

.section-header{

    height:54px;

    display:flex;

    justify-content:space-between;

    align-items:center;

    padding:0 14px;

    background:
    linear-gradient(
        180deg,
        #262626,
        #101010
    );

    border-bottom:1px solid #2f2f2f;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.05);

}

/* LEFT */

.header-left{

    display:flex;

    align-items:center;

    gap:10px;

}

.header-icon{

    width:32px;

    height:32px;

    border-radius:8px;

    display:flex;

    justify-content:center;

    align-items:center;

    background:
    linear-gradient(
        180deg,
        #1d4ed8,
        #1e3a8a
    );

    color:white;

    font-size:14px;

    box-shadow:
        0 2px 8px rgba(37,99,235,.35);

}

.header-title{

    display:flex;

    flex-direction:column;

}

.title-main{

    font-size:14px;

    font-weight:600;

    color:white;

    line-height:16px;

}

.title-sub{

    font-size:11px;

    color:#bfbfbf;

}

/* RIGHT */

.header-right{

    display:flex;

    align-items:center;

    gap:10px;

}

/* SEARCH */

.header-search{

    width:260px;

    height:34px;

    display:flex;

    align-items:center;

    padding:0 10px;

    border-radius:8px;

    background:#181818;

    border:1px solid #343434;

    transition:.25s;

}

.header-search:focus-within{

    border-color:#3b82f6;

    box-shadow:
        0 0 0 3px rgba(59,130,246,.18);

}

.header-search i{

    color:#8e8e8e;

    font-size:12px;

    margin-right:8px;

}

.header-search input{

    width:100%;

    border:none;

    outline:none;

    background:none;

    color:white;

    font-size:12px;

}

.header-search input::placeholder{

    color:#8b8b8b;

}

/* CLOSE BUTTON */

.popup-close-btn{

    width:34px;

    height:34px;

    border:none;

    border-radius:8px;

    background:#1b1b1b;

    color:#d7d7d7;

    cursor:pointer;

    transition:.2s;

}

.popup-close-btn:hover{

    background:#d92d20;

    color:white;

}
    </style>
</head>

<body>

<div class="dashboard-layout">

    <!-- ================= SIDEBAR ================= -->

    <aside class="sidebar">

        <div class="sidebar-logo">

            <img src="icon.png" alt="Logo">

            <span>Livestock Admin</span>

        </div>

        <ul class="sidebar-menu">

            <li class="active">
                <i class="fa-solid fa-chart-pie"></i>
                Dashboard
            </li>

            <li onclick="openUsersPopup()">
                <i class="fa-solid fa-users"></i>
                Users
            </li>

            <li onclick="openDiptankPopup()">
                <i class="fa-solid fa-warehouse"></i>
                Diptanks
            </li>

            <li>
                <i class="fa-solid fa-cow"></i>
                Animals
            </li>

            <li>
                <i class="fa-solid fa-house"></i>
                Kraals
            </li>

            <button class="open-eartag-btn" onclick="openEartagPopup()">
    		<i class="fa-solid fa-tags"></i>
    		Eartagging
	    </button>

            <li>
                <i class="fa-solid fa-file-lines"></i>
                Reports
            </li>

            <li>
                <i class="fa-solid fa-bullhorn"></i>
                Announcements
            </li>

            <li>
                <i class="fa-solid fa-gear"></i>
                Settings
            </li>

        </ul>

    </aside>

    <!-- ================= MAIN AREA ================= -->

    <div class="main-area">

        <!-- TOP BAR -->

        <div class="topbar">

            <div class="search-box">

                <input
                    type="text"
                    placeholder="Search users, animals, reports...">

            </div>

            <div class="topbar-right">

    <!-- Announcements -->
    <div class="icon-box" onclick="toggleAnnouncements()">
    <i class="fa-solid fa-bullhorn"></i>

    <?php if($unreadAnnouncements > 0){ ?>
        <div class="badge" id="announcementBadge">
            <?php echo $unreadAnnouncements; ?>
        </div>
    <?php } ?>
</div>

    <!-- Notification -->
<?php

$user_id = $_SESSION["id"];

$countQuery = mysqli_query(
    $link,
    "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = '$user_id'
    AND is_read = 0
    "
);

$countData = mysqli_fetch_assoc($countQuery);

$notificationCount =
    $countData['total'];

?>
   <div class="icon-box"
     onclick="openNotifications()">

    <i class="fa-solid fa-bell"></i>

    <?php if($notificationCount > 0){ ?>

        <div class="badge"
             id="notificationBadge">

            <?php echo $notificationCount; ?>

        </div>

    <?php } ?>

</div>
<!-- Messages -->
<div class="icon-box" onclick="toggleMessagesPanel()">

<?php
include_once "config.php";

$currentUser = $_SESSION["id"];

$sql = "
SELECT COUNT(*) AS unread
FROM messages
WHERE receiver_id = '$currentUser'
AND is_read = 0
";

$result = mysqli_query($link, $sql);

if (!$result) {
    die(mysqli_error($link));
}

$row = mysqli_fetch_assoc($result);
$unreadMessages = $row['unread'];
?>

    <i class="fa-solid fa-envelope"></i>
 <div class="badge" id="unreadMessagesBadge" style="display:none;"></div>

    <?php if ($unreadMessages > 0) { ?>
        <div class="badge" id="unreadMessagesBadge">
            <?php echo $unreadMessages; ?>
        </div>
    <?php } ?>

</div>

                <div class="profile">

                    <div class="profile-icon">

                        <i class="fa-solid fa-user"></i>

                    </div>

                    <span>
                        <?php echo htmlspecialchars($_SESSION["username"]); ?>
                    </span>

                </div>
<a href="home.php" class="back-btn">

<!-- Font Awesome CDN (add in <head>) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<button class="door-btn" onclick="goToApp()">
    <i class="fa fa-door-open"></i>
    <span>Back to App</span>
</button>

</a>

            </div>

        </div>

        <!-- DASHBOARD CONTENT -->

        <main class="dashboard-content">

            <!-- STATISTICS -->

            <div class="dashboard-row">

                <div class="dashboard-card">

                    <h4>TOTAL CATTLE</h4>

                    <div class="stat-value">12,450</div>

                </div>

                <div class="dashboard-card">

                    <h4>SMALL STOCK</h4>

                    <div class="stat-value">4,320</div>

                </div>

                <div class="dashboard-card">

                    <h4>DIPTANKS</h4>

                    <div class="stat-value">78</div>

                </div>

                <div class="dashboard-card">

                    <h4>ACTIVE USERS</h4>

                    <div class="stat-value">125</div>

                </div>

            </div>

            <!-- SECOND ROW -->

            <div class="content-grid">

                <!-- CHART -->
<div class="dashboard-card chart-card">

    <div class="chart-header">

        <h4>Animal Registration Trend</h4>

        <!-- white icon only -->
        <button class="expand-btn" onclick="openChartPopup()">
           <i class="fa-solid fa-arrows-maximize"></i>
        </button>

    </div>

    <div id="chartBox"></div>
</div>


<!-- WINDOWS 11 STYLE POPUP -->
<div id="chartPopup" class="win-popup">

    <div class="win-window">

        <!-- TITLE BAR -->
        <div class="win-titlebar">

            <div class="win-title">
                <i class="fa-solid fa-chart-line"></i>
                Animal Registration Analytics
            </div>

            <div class="win-controls">
                <button onclick="closeChartPopup()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

        </div>

        <!-- BODY -->
        <div class="win-body">

            <!-- LEFT: CHART -->
            <div class="win-chart">
                <div id="expandedChartBox"></div>
 <div class="chart-toggles">

    <span class="chart-toggle-label">
        <i class="fa-solid fa-filter"></i>
       Select data to display:
    </span>

    <button class="chart-toggle active"
            onclick="setChartView('all', this)">
        All Lines
    </button>

    <button class="chart-toggle"
            onclick="setChartView('permit_in', this)">
        Permits In
    </button>

    <button class="chart-toggle"
            onclick="setChartView('permit_out', this)">
        Permits Out
    </button>

    <button class="chart-toggle"
            onclick="setChartView('births', this)">
        Births
    </button>

    <button class="chart-toggle"
            onclick="setChartView('deaths', this)">
        Deaths
    </button>

</div>
<div class="chart-settings">

    <div class="axis-options">

    <span class="chart-setting-title">
        <i class="fa-solid fa-ruler-horizontal"></i>
      Control X-Axis Display
    </span>



        <button class="axis-btn active"
                onclick="setAxisInterval(1, this)">
            Every Day
        </button>

        <button class="axis-btn"
                onclick="setAxisInterval(2, this)">
            Every 2 Days
        </button>

        <button class="axis-btn"
                onclick="setAxisInterval(5, this)">
            Every 5 Days
        </button>

        <button class="axis-btn"
                onclick="setAxisInterval(10, this)">
            Every 10 Days
        </button>

    </div>
<div class="chart-settings">

   <div class="axis-options">

        <span class="chart-setting-title">
            <i class="fa-solid fa-layer-group"></i>
            Chart Display Options
        </span>

        <button class="axis-btn active"
                onclick="setChartOption('grid', this)">
            Grid Lines
        </button>

        <button class="axis-btn active"
                onclick="setChartOption('dots', this)">
            Data Points
        </button>

    </div>


</div>

</div>
            </div>

            <!-- RIGHT: STATS -->
           <div class="region-tabs-container">

  <div class="region-tabs-header">

        <button class="region-tab active"
                onclick="openRegionTab(event,'hhohho')">
            Hhohho
        </button>

        <button class="region-tab"
                onclick="openRegionTab(event,'manzini')">
            Manzini
        </button>

        <button class="region-tab"
                onclick="openRegionTab(event,'lubombo')">
            Lubombo
        </button>

        <button class="region-tab"
                onclick="openRegionTab(event,'shiselweni')">
            Shiselweni
        </button>

    </div>

    <div class="region-content">

        <div id="hhohho" class="region-pane active-pane">
            Hhohho content goes here
        </div>

        <div id="manzini" class="region-pane">
            Manzini content goes here
        </div>

        <div id="lubombo" class="region-pane">
            Lubombo content goes here
        </div>

        <div id="shiselweni" class="region-pane">
            Shiselweni content goes here
        </div>

    </div>

        </div>
</div>

    </div>

</div>
</div>



 <!-- REPORTS TABLE -->

<div class="content-grid">

            <div class="dashboard-card reports-card">
                <h4>

                    <i class="fa-solid fa-clock-rotate-left"></i>

                    Recent Activity

                </h4>

               

                <table class="dashboard-table">
    <thead>
        <tr>
            <th>User</th>
            <th>Status</th>
            <th>Time</th>
        </tr>
    </thead>

    <tbody id="userActivityBody">
        <!-- PHP rows will be loaded here -->
    </tbody>
</table>
</div>


 <!-- RECENT ACTIVITY -->

            <div class="dashboard-card activity-card">

		 <h4>

                    <i class="fa-solid fa-file-lines"></i>

                    Latest Reports

                </h4>

                <table class="dashboard-table">

                    <thead>

                    <tr>

                        <th>User</th>

                        <th>Action</th>

                        <th>Date</th>

                    </tr>

                    </thead>

                    <?php
include_once "config.php";

$query = "SELECT id, username, role, status, last_seen FROM users ORDER BY id DESC";
$result = mysqli_query($link, $query);
?>

<tbody id="usersActivityBody">

<?php while($row = mysqli_fetch_assoc($result)) { 

    $status = $row['status'];
    $last_seen = $row['last_seen'];

    // decide colors + labels
    if($status == "Active"){
        $status_icon = "🟢";
        $status_text = "Active";
        $status_color = "green";
        $time_label = "Signed in at";
    } else {
        $status_icon = "🔴";
        $status_text = "Inactive";
        $status_color = "red";
        $time_label = "Last seen";
    }
?>

<tr style="border-left:4px solid <?php echo $status_color; ?>;">

    <!-- USER -->
    <td>
        <i class="fa-solid fa-user"></i>
        <?php echo htmlspecialchars($row['username']); ?>
    </td>

    <!-- STATUS -->
    <td>
        <span style="color:<?php echo $status_color; ?>; font-weight:bold;">
            <?php echo $status_icon . " " . $status_text; ?>
        </span>
    </td>

    <!-- TIME -->
    <td>
        <i class="fa-solid fa-clock"></i>
        <span style="color:<?php echo $status_color; ?>;">
            <?php echo $time_label . ": " . $last_seen; ?>
        </span>
    </td>

</tr>

<?php } ?>

</tbody>
                </table>

            </div>
            </div>

        </main>


<div id="messagesPanel" class="messages-panel">

    <div class="messages-header">

        <div class="messages-user">
            <div class="message-avatar-header">
                <i class="fa-solid fa-user"></i>
            </div>
            <span>Messages</span>
        </div>

        <div class="messages-actions">

            <button onclick="openNewMessagePanel()"
                    class="header-btn"
                    title="New Message">
                <i class="fa-solid fa-pen-to-square"></i>
            </button>

            <button onclick="toggleMessagesPanel()"
                    class="header-btn"
                    title="Close">
                <i class="fa-solid fa-chevron-down"></i>
            </button>

        </div>

    </div>

   <div class="messages-list" id="messagesList">
    <?php include "get_conversations.php"; ?>
</div>
</div>

<div id="newMessagePanel" class="new-message-panel">

    <div class="new-message-header">

        <h3>New Message</h3>

        <button class="header-btn"
                onclick="closeNewMessage()">

            <i class="fa-solid fa-xmark"></i>

        </button>

    </div>

<div class="new-message-users" id="newMessageUsers">

</div>
</div>

<div id="eartagPopup" class="eartag-popup">

    <div class="eartag-popup-box">

        <div class="dashboard-section">

            <!--==================================================
                HEADER
            ==================================================-->

<div class="section-header">

    <div class="header-left">

        <div class="header-icon">

            <i class="fa-solid fa-tags"></i>

        </div>

        <div class="header-title">

            <span class="title-main">
                Eartag Management
            </span>

            <span class="title-sub">
                Manage Pending, Approved, Applied & Returned Eartags
            </span>

        </div>

    </div>

    <div class="header-right">

        <div class="header-search">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                id="damSearch"
                maxlength="8"
                placeholder="Search Dam ID (000-0000)"
                onkeyup="filterDamID(this.value)">

        </div>

        <button
            class="popup-close-btn"
            onclick="closeEartagPopup()">

            <i class="fa-solid fa-xmark"></i>

        </button>

    </div>

</div>


            <!--==================================================
                TABS
            ==================================================-->

            <div class="eartag-tabs">

                <button
                    class="eartag-tab active"
                    onclick="showEartagTab('pendingTab',this)">

                    Pending Requests

                </button>

                <button
                    class="eartag-tab"
                    onclick="showEartagTab('approvedTab',this)">

                    Approved

                </button>

                <button
                    class="eartag-tab"
                    onclick="showEartagTab('appliedTab',this)">

                    Applied

                </button>

                <button
                    class="eartag-tab"
                    onclick="showEartagTab('returnedTab',this)">

                    Returned

                </button>

            </div>

            <!--==================================================
                PENDING REQUESTS
            ==================================================-->

            <div
                id="pendingTab"
                class="eartag-content active-tab">

                <div class="records-table-wrapper">

                    <div class="table-container">

                        <table class="records-table">

                            <thead>

                                <tr>

                                    <th>Animal ID</th>

                                    <th>Dam ID</th>

                                    <th>Kraal No.</th>

                                    <th>Owner Name</th>

                                    <th>Sex</th>

                                    <th>Colour</th>

                                    <th>Date of Birth</th>

                                    <th>Eartag Number</th>

                                    <th>Action</th>

                                </tr>

                            </thead>

                            <tbody>

<?php

$sql = "
SELECT *
FROM eartag_requests
WHERE status='Pending'
ORDER BY requested_date DESC
";

$result = mysqli_query($link, $sql);

if(mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

?>

                                <tr>

                                    <td>
                                        <?php echo $row["animal_id"]; ?>
                                    </td>

                                    <td>
                                        <?php echo $row["dam_id"]; ?>
                                    </td>

                                    <td>
                                        <?php echo $row["kraal_no"]; ?>
                                    </td>

                                    <td>
                                        <?php echo $row["owner_name"]; ?>
                                    </td>

                                    <td>
                                        <?php echo $row["sex"]; ?>
                                    </td>

                                    <td>
                                        <?php echo $row["color"]; ?>
                                    </td>

                                    <td>
                                        <?php echo $row["date_of_birth"]; ?>
                                    </td>

                                    <td>

                                        <input
                                            type="text"
                                            class="eartag-input"
                                            id="tag_<?php echo $row['id']; ?>"
                                            placeholder="PH341-0001">

                                    </td>

                                    <td>

                                        <button
                                            class="approve-btn"
                                            onclick="approveEartag(<?php echo $row['id']; ?>)">

                                            Approve

                                        </button>

                                        <button
                                            class="reject-btn"
                                            onclick="rejectEartag(<?php echo $row['id']; ?>)">

                                            Decline

                                        </button>

                                    </td>

                                </tr>

<?php

    }

}else{

?>

                                <tr>

                                    <td
                                        colspan="9"
                                        class="no-data">

                                        No Pending Eartag Requests

                                    </td>

                                </tr>

<?php

}

?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <!--==================================================
                APPROVED TAB STARTS IN PART 2
            ==================================================-->

            <div
                id="approvedTab"
                class="eartag-content">
                <div class="records-table-wrapper">

                    <div class="table-container">

                        <table class="records-table">

                            <thead>

                                <tr>

                                    <th>Eartag Number</th>

                                    <th>Animal ID</th>

                                    <th>Dam ID</th>

                                    <th>Kraal No.</th>

                                    <th>Owner Name</th>

                                    <th>Sex</th>

                                    <th>Colour</th>

                                    <th>Date of Birth</th>

                                    <th>Approved Date</th>

                                </tr>

                            </thead>

                            <tbody>

<?php

$sql = "
SELECT *
FROM eartag_requests
WHERE status='Approved'
ORDER BY approved_date DESC
";

$result = mysqli_query($link, $sql);

if(mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

?>

                                <tr>

                                    <td>

                                        <strong>

                                            <?php echo $row["eartag_number"]; ?>

                                        </strong>

                                    </td>

                                    <td>

                                        <?php echo $row["animal_id"]; ?>

                                    </td>

                                    <td>

                                        <?php echo $row["dam_id"]; ?>

                                    </td>

                                    <td>

                                        <?php echo $row["kraal_no"]; ?>

                                    </td>

                                    <td>

                                        <?php echo $row["owner_name"]; ?>

                                    </td>

                                    <td>

                                        <?php echo $row["sex"]; ?>

                                    </td>

                                    <td>

                                        <?php echo $row["color"]; ?>

                                    </td>

                                    <td>

                                        <?php echo $row["date_of_birth"]; ?>

                                    </td>

                                    <td>

                                        <?php echo $row["approved_date"]; ?>

                                    </td>

                                </tr>

<?php

    }

}else{

?>

                                <tr>

                                    <td
                                        colspan="9"
                                        class="no-data">

                                        No Approved Eartags

                                    </td>

                                </tr>

<?php

}

?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <!--==================================================
                APPLIED TAB
            ==================================================-->

            <div
                id="appliedTab"
                class="eartag-content">
                <div class="records-table-wrapper">

                    <div class="table-container">

                        <table class="records-table">

                            <thead>

                                <tr>

                                    <th>Eartag Number</th>

                                    <th>Animal ID</th>

                                    <th>Dam ID</th>

                                    <th>Kraal No.</th>

                                    <th>Owner Name</th>

                                    <th>Sex</th>

                                    <th>Colour</th>

                                    <th>Date Applied</th>

                                    <th>Applied By</th>

                                </tr>

                            </thead>

                            <tbody>

<?php

$sql = "
SELECT *
FROM eartag_requests
WHERE status='Applied'
ORDER BY applied_date DESC
";

$result = mysqli_query($link, $sql);

if(mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

?>

                                <tr>

                                    <td><?php echo $row["eartag_number"]; ?></td>

                                    <td><?php echo $row["animal_id"]; ?></td>

                                    <td><?php echo $row["dam_id"]; ?></td>

                                    <td><?php echo $row["kraal_no"]; ?></td>

                                    <td><?php echo $row["owner_name"]; ?></td>

                                    <td><?php echo $row["sex"]; ?></td>

                                    <td><?php echo $row["color"]; ?></td>

                                    <td><?php echo $row["applied_date"]; ?></td>

                                    <td><?php echo $row["applied_by"]; ?></td>

                                </tr>

<?php

    }

}else{

?>

                                <tr>

                                    <td colspan="9" class="no-data">

                                        No Applied Eartags

                                    </td>

                                </tr>

<?php

}

?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <!--==================================================
                RETURNED TAB
            ==================================================-->

            <div
                id="returnedTab"
                class="eartag-content">

                <div class="records-table-wrapper">

                    <div class="table-container">

                        <table class="records-table">

                            <thead>

                                <tr>

                                    <th>Eartag Number</th>

                                    <th>Animal ID</th>

                                    <th>Reason Returned</th>

                                    <th>Returned Date</th>

                                    <th>Returned By</th>

                                </tr>

                            </thead>

                            <tbody>

<?php

$sql = "
SELECT *
FROM eartag_requests
WHERE status='Returned'
ORDER BY returned_date DESC
";

$result = mysqli_query($link, $sql);

if(mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

?>

                                <tr>

                                    <td><?php echo $row["eartag_number"]; ?></td>

                                    <td><?php echo $row["animal_id"]; ?></td>

                                    <td><?php echo $row["return_reason"]; ?></td>

                                    <td><?php echo $row["returned_date"]; ?></td>

                                    <td><?php echo $row["returned_by"]; ?></td>

                                </tr>

<?php

    }

}else{

?>

                                <tr>

                                    <td colspan="5" class="no-data">

                                        No Returned Eartags

                                    </td>

                                </tr>

<?php

}

?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div> <!-- dashboard-section -->

    </div> <!-- eartag-popup-box -->

</div> <!-- eartagPopup -->


<div id="chatPanel" class="chat-panel">

    <div class="chat-header">

        <div class="chat-user">

            <div class="user-avatar">

                <i class="fa-solid fa-user"></i>

                <span id="chatUserStatus" class="user-status-dot offline"></span>

            </div>

            <div class="chat-user-details">

                <span id="chatUserName">Conversation</span>

                <small id="chatUserPresence" class="chat-user-presence">
                    Offline
                </small>

            </div>

        </div>

        <button class="header-btn" onclick="closeChatPanel()">
            <i class="fa-solid fa-xmark"></i>
        </button>

    </div>

    <div class="chat-body" id="chatBody">

        <!-- Messages load here -->

    </div>

    <div class="chat-footer">

        <input
            type="text"
            id="messageText"
            placeholder="Type a message...">

        <button onclick="sendMessage()">
            <i class="fa-solid fa-paper-plane"></i>
        </button>

    </div>

</div>

<div id="pageLoader">

    <div class="loader-card">

        <span class="loader-label">Loading</span>

        <div class="mini-spinner"></div>

    </div>

</div>

<div id="announcementsPanel" class="announcements-panel">

    <div class="announcements-header">

    <h3>
        <i class="fa-solid fa-bullhorn"></i>
        Announcements
    </h3>

    <button onclick="toggleAnnouncements()">
        <i class="fa-solid fa-xmark"></i>
    </button>

</div>

<?php if($_SESSION["role"] == "admin"){ ?>

<div class="announcement-actions">

    <button
        class="add-announcement-btn"
        onclick="openAnnouncementForm()">

        <i class="fa-solid fa-plus"></i>
        Add Announcement

    </button>

</div>

<?php } ?>

    <div class="announcements-list">

<?php

include_once "config.php";

$sql = "SELECT *
        FROM announcements
        ORDER BY created_at DESC";

$result = mysqli_query($link, $sql);

if(mysqli_num_rows($result) > 0)
{
    while($row = mysqli_fetch_assoc($result))
    {
?>

        <div class="announcement-item">

            <strong>
                <?php echo htmlspecialchars($row["title"]); ?>
            </strong>

            <p>
                <?php echo nl2br(htmlspecialchars($row["message"])); ?>
            </p>

            <small>
                <?php echo $row["created_at"]; ?>
            </small>

        </div>

<?php
    }
}
else
{
    echo "<div style='padding:15px;'>No announcements available.</div>";
}

?>

</div> <!-- announcements-list -->
</div> <!-- announcementsPanel -->

<div id="announcementPopup" class="popup-overlay">

    <div class="popup-box">

        <div class="popup-header">

            <h2>New Announcement</h2>

            <button onclick="closeAnnouncementForm()">
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>

        <input
            type="text"
            id="announcementTitle"
            placeholder="Title"
            style="width:100%;margin-bottom:10px;">

        <textarea
            id="announcementMessage"
            placeholder="Announcement message"
            style="width:100%;height:120px;"></textarea>

        <button onclick="saveAnnouncement()">
            Publish
        </button>

    </div>

</div>

<div id="usersPopup" class="popup-overlay">

    <div class="popup-box users-popup win11-users-popup">
        <!-- Header -->
        <div class="popup-header">

            <h2>
                <i class="fa-solid fa-users"></i>
                System Users
            </h2>

            <button type="button"
                    class="popup-close-btn"
                    onclick="closeUsersPopup()">

                <i class="fa-solid fa-times"></i>

            </button>

        </div>

        <!-- Toolbar -->
        <div class="users-toolbar">

            <input type="text"
                   id="userSearch"
                   placeholder="Search users..."
                   onkeyup="filterUsersTable()">

            <button type="button" class="toolbar-btn">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

        </div>

        <!-- Table -->
        <div class="users-table-container">

            <table class="users-table" id="usersTable">

                <thead>

                    <tr>
                        <th width="60">ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Surname</th>
                        <th width="130">Role</th>
                        <th width="120">Actions</th>
                    </tr>

                </thead>

                <tbody>

<?php
include_once "config.php";

$currentUserId = $_SESSION['id'];

/* CURRENT USER */
$sql = "SELECT role, station FROM users WHERE id = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $currentUserId);
$stmt->execute();

$currentUser = $stmt->get_result()->fetch_assoc();

$role    = $currentUser['role'];
$station = $currentUser['station'];

/* ROLE ACCESS */

if ($role === 'super_admin') {

    $sql = "SELECT * FROM users ORDER BY id DESC";
    $result = mysqli_query($link, $sql);

}
elseif ($role === 'hooper_admin') {

    $sql = "
        SELECT *
        FROM users
        WHERE role='user'
        AND designation='veterinary assistant'
        AND station=?
        ORDER BY id DESC
    ";

    $stmt = $link->prepare($sql);
    $stmt->bind_param("s", $station);
    $stmt->execute();

    $result = $stmt->get_result();

}
else {

    die("Access denied");

}

while($user = mysqli_fetch_assoc($result)){
?>

                    <tr>

                        <td>
                            <?php echo $user['id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($user['name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($user['surname']); ?>
                        </td>

                        <td>

                            <span class="role-badge">

                                <?php echo htmlspecialchars($user['role']); ?>

                            </span>

                        </td>

                        <td>

                            <button
                                class="action-btn edit-btn"
                                title="Edit User"

                                onclick="openEditUser(
                                '<?php echo $user['id']; ?>',
                                '<?php echo htmlspecialchars($user['username']); ?>',
                                '<?php echo htmlspecialchars($user['name']); ?>',
                                '<?php echo htmlspecialchars($user['surname']); ?>',
                                '<?php echo htmlspecialchars($user['role']); ?>',
                                '<?php echo htmlspecialchars($user['designation']); ?>',
                                '<?php echo htmlspecialchars($user['station']); ?>'
                                )">

                                <i class="fa-solid fa-pen-to-square"></i>

                            </button>

                            <button
                                class="action-btn key-btn"
                                title="Reset Password">

                                <i class="fa-solid fa-key"></i>

                            </button>

                        </td>

                    </tr>

<?php
}
?>

                </tbody>

            </table>

        </div>

<div class="users-footer">

    <span id="userCount">
        Total Users: <?php echo mysqli_num_rows($result); ?>
    </span>

    <button type="button"
            class="save-btn"
            onclick="closeUsersPopup()">

         <i class="fa-solid fa-xmark"></i>
        Close

    </button>

</div>

    </div>

</div>

<!-- EDIT USER POPUP -->

<div id="editUserPopup" class="popup-overlay">

    <div class="popup-box fixed-popup">

        <!-- Header -->
        <div class="popup-header">

            <h2>
                <i class="fa-solid fa-user-pen"></i>
                Edit User
            </h2>

            <button type="button"
        class="win-close-btn"
        onclick="closeEditUserPopup()">

    <i class="fa-solid fa-xmark"></i>

</button>
        </div>

        <input type="hidden" id="editUserId">

        <form id="editUserForm">

            <!-- Scrollable Content -->

            <div class="popup-body">

                <div class="form-group">

                    <label>Username</label>

                    <input type="text"
                           id="editUsername">

                </div>

                <div class="form-group">

                    <label>Name</label>

                    <input type="text"
                           id="editName">

                </div>

                <div class="form-group">

                    <label>Surname</label>

                    <input type="text"
                           id="editSurname">

                </div>

                <div class="form-group">

                    <label>Role</label>

                    <select id="editRole">

                        <option value="user">
                            User
                        </option>

                        <option value="supervisor">
                            Supervisor
                        </option>

                        <option value="admin">
                            Admin
                        </option>

                        <option value="hooper_admin">
                            Hooper Admin
                        </option>

                        <option value="super_admin">
                            Super Admin
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label>Designation</label>

                    <select id="editDesignation">

                        <option value="">
                            Select Designation
                        </option>

                        <option value="Veterinarian">
                            Veterinarian
                        </option>

                        <option value="Veterinary Assistant">
                            Veterinary Assistant
                        </option>

                        <option value="Animal Health Inspector">
                            Animal Health Inspector
                        </option>

                        <option value="Livestock Officer">
                            Livestock Officer
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label>Station</label>

                    <select id="editStation">

                        <option value="">
                            Select Station
                        </option>

                        <option value="Mbabane Vet">
                            Mbabane Vet
                        </option>

                        <option value="Manzini Vet">
                            Manzini Vet
                        </option>

                        <option value="Piggs Peak Vet">
                            Piggs Peak Vet
                        </option>

                        <option value="Nhlangano Vet">
                            Nhlangano Vet
                        </option>

                        <option value="Siteki Vet">
                            Siteki Vet
                        </option>

                        <option value="Big Bend Vet">
                            Big Bend Vet
                        </option>

                    </select>

                </div>

            </div>

            <!-- Footer -->

            <div class="form-actions">

                <button type="button"
                        class="cancel-btn"
                        onclick="closeEditUserPopup()">

                    <i class="fa-solid fa-xmark"></i>
                    Cancel

                </button>

                <button type="button"
                        class="save-btn"
                        onclick="saveUserChanges()">

                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Changes

                </button>

            </div>

        </form>

    </div>

</div>
<!-- SAVING POPUP -->
<div id="savingPopup" class="popup-overlay">

    <div class="status-popup">

        <i class="fa-solid fa-spinner fa-spin status-saving-icon"></i>

        <div class="status-title">
            Saving Changes
        </div>

        <div class="status-message">
            Please wait...
        </div>

    </div>

</div>

<!-- SUCCESS POPUP -->
<div id="successPopup" class="popup-overlay">

    <div class="status-popup">

        <i class="fa-solid fa-circle-check status-success-icon"></i>

        <div class="status-title">
            Success
        </div>

        <div class="status-message">
            User details updated successfully.
        </div>

        <button class="status-btn success-btn"
                onclick="closeSuccessPopup()">
            OK
        </button>

    </div>

</div>

<!-- ERROR POPUP -->
<div id="errorPopup" class="popup-overlay">

    <div class="status-popup">

        <i class="fa-solid fa-circle-xmark status-error-icon"></i>

        <div class="status-title">
            Error
        </div>

        <div id="errorMessage"
             class="status-message">

            Something went wrong.
        </div>

        <button class="status-btn error-btn"
                onclick="closeErrorPopup()">
            Close
        </button>

    </div>

</div>
<div id="diptankPopup" class="popup-overlay">

    <div class="popup-box users-popup win11-users-popup">

<!-- Header -->
<div class="popup-header">

    <h2 class="popup-title">
        <i class="fa-solid fa-water"></i>
        Diptank Management
    </h2>

    <div class="popup-header-actions">

        <button type="button"
        class="create-diptank-btn"
        onclick="openAddDiptankPopup()">

    <i class="fa-solid fa-plus"></i>
    Add Dip Tank

</button>

        <button type="button"
                class="popup-close-btn"
                onclick="closeDiptankPopup()"
                title="Close">

            <i class="fa-solid fa-xmark"></i>

        </button>

    </div>

</div>

        <!-- Toolbar -->
        <div class="users-toolbar">

            <input type="text"
                   id="diptankSearch"
                   placeholder="Search diptank..."
                   onkeyup="filterDiptankTable()">

            <button type="button" class="toolbar-btn">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

        </div>

        <!-- Table -->
<!-- Table -->
<div class="users-table-container">

    <table class="users-table" id="diptankTable">

        <thead>

            <tr>
                <th width="120">Tank No</th>
                <th>Diptank Name</th>
                <th>Location</th>
                <th width="140">Overseer</th>
                <th width="140">Supervisor</th>
            </tr>

        </thead>

        <tbody>

        <?php

        include_once "config.php";

        $sql = "
            SELECT *
            FROM diptanks
            ORDER BY diptank_number ASC
        ";

        $result = mysqli_query($link,$sql);

        while($row = mysqli_fetch_assoc($result))
        {
        ?>

            <tr>

                <td>
                    <span class="table-badge">
                        <?php echo htmlspecialchars($row['diptank_number']); ?>
                    </span>
                </td>

                <td>
                    <?php echo htmlspecialchars($row['diptank_name']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($row['location']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($row['overseer']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($row['supervisor']); ?>
                </td>

            </tr>

        <?php
        }
        ?>

        </tbody>

    </table>

</div>

<!-- Footer -->
<div class="users-footer">

    <span>
        Total Diptanks:
        <?php echo mysqli_num_rows($result); ?>
    </span>

    <button type="button"
            class="save-btn"
            onclick="closeDiptankPopup()">

         <i class="fa-solid fa-xmark"></i>
        Close

    </button>

</div>

    </div>

</div>


<div id="addDiptankPopup" class="popup-overlay">

    <div class="popup-box fixed-popup">

        <!-- Header -->
        <div class="popup-header">

            <h2>
                <i class="fa-solid fa-water"></i>
                Add New Diptank
            </h2>

<button type="button"
        class="win-close-btn"
        onclick="closeAddDiptankPopup()"
        title="Close">

    <i class="fa-solid fa-xmark"></i>

</button>
        </div>

        <!-- Form -->
        <form id="diptankForm">

            <!-- Scrollable Body -->
            <div class="popup-body">

                <div class="form-group">

                    <label>Diptank Name</label>

                    <input
                        type="text"
                        name="diptank_name"
                        required>

                </div>

                <div class="form-group">

                    <label>Diptank Number</label>

                    <input
                        type="text"
                        name="diptank_number"
                        required>

                </div>

                <div class="form-group">

                    <label>Location</label>

                    <input
                        type="text"
                        name="location">

                </div>

                <div class="form-group">

                    <label>Region</label>

                    <select name="region" required>

                        <option value="">
                            Select Region
                        </option>

                        <option value="Manzini">
                            Manzini
                        </option>

                        <option value="Shiselweni">
                            Shiselweni
                        </option>

                        <option value="Hhohho">
                            Hhohho
                        </option>

                        <option value="Lubombo">
                            Lubombo
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label>Overseer</label>

                    <select name="overseer" required>

                        <option value="">
                            Select Overseer
                        </option>

                        <?php

                        $sqlUsers =
                            "SELECT id,name,surname
                             FROM users
                             ORDER BY name ASC";

                        $usersResult =
                            mysqli_query($link,$sqlUsers);

                        while($user =
                            mysqli_fetch_assoc($usersResult))
                        {
                        ?>

                        <option value="<?php echo $user['id']; ?>">

                            <?php
                            echo htmlspecialchars(
                                $user['name'].' '.$user['surname']
                            );
                            ?>

                        </option>

                        <?php
                        }
                        ?>

                    </select>

                </div>

                <div class="form-group">

                    <label>Supervisor</label>

                    <select name="supervisor" required>

                        <option value="">
                            Select Supervisor
                        </option>

                        <?php

                        mysqli_data_seek(
                            $usersResult,
                            0
                        );

                        while($user =
                            mysqli_fetch_assoc($usersResult))
                        {
                        ?>

                        <option value="<?php echo $user['id']; ?>">

                            <?php
                            echo htmlspecialchars(
                                $user['name'].' '.$user['surname']
                            );
                            ?>

                        </option>

                        <?php
                        }
                        ?>

                    </select>

                </div>

            </div>

            <!-- Fixed Footer -->
            <div class="form-actions">

                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeAddDiptankPopup()">

                    <i class="fa-solid fa-xmark"></i>
                    Cancel

                </button>

                <button
                    type="button"
                    class="save-btn"
                    onclick="saveDiptank()">

                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Diptank

                </button>

            </div>

        </form>

    </div>

</div>

</div>
<div id="notificationsPopup" class="popup-overlay">

    <div class="popup-box notifications-popup">

        <div class="notifications-header">

            <h3>
                <i class="fa-solid fa-bell"></i>
                Notifications
            </h3>

            <button onclick="closeNotifications()">
                ×
            </button>

        </div>

        <div id="notificationsList" class="notifications-body">

<?php

$sql = "
SELECT *
FROM notifications
WHERE user_id = '$user_id'
ORDER BY created_at DESC
";

$result = mysqli_query($link,$sql);

while($row = mysqli_fetch_assoc($result))
{
?>

    <div class="notification-card">

        <div class="notification-title">
            <?php echo htmlspecialchars($row['title']); ?>
        </div>

        <div class="notification-message">
            <?php echo htmlspecialchars($row['message']); ?>
        </div>

        <div class="notification-time">
            <i class="fa-regular fa-clock"></i>
            <?php echo date("d M Y H:i", strtotime($row['created_at'])); ?>
        </div>

    </div>

<?php
}
?>

        </div>

    </div>

</div>

<script>

function openNewMessage(){

    alert("Open New Message Window");

}
function openNewMessagePanel(){

    document
        .getElementById("newMessagePanel")
        .classList
        .add("active");

    savePanelState();
}

function closeNewMessage(){

    document
        .getElementById("newMessagePanel")
        .classList
        .remove("active");

    savePanelState();

    // Move chat panel next to messages panel if open
    if(document.getElementById("chatPanel").classList.contains("active"))
    {
        document.getElementById("chatPanel").style.right = "380px";
    }
}
let activeUser = 0;

function loadMessages(userId)
{
    fetch("get_messages.php?user_id=" + userId)
    .then(response => response.text())
    .then(data => {

        const chatBody =
            document.getElementById("chatBody");

        const nearBottom =
            chatBody.scrollHeight -
            chatBody.scrollTop -
            chatBody.clientHeight < 50;

        chatBody.innerHTML = data;

        if(nearBottom)
        {
            chatBody.scrollTop =
                chatBody.scrollHeight;
        }
    });
}
</script>

<script>

function openConversation(userId, userName, isOnline)
{
    activeUser = userId;

    // Mark messages from this user as read
    fetch(
        "mark_messages_read.php?user_id=" + userId
    )
    .then(response => response.text())
    .then(() => {

        updateUnreadBadge();

        refreshConversations();

    });

document.getElementById("chatUserName").textContent = userName;

const dot = document.getElementById("chatUserStatus");
const presence = document.getElementById("chatUserPresence");

if (isOnline) {

    dot.classList.remove("offline");
    dot.classList.add("online");

    presence.textContent = "Active";
    presence.classList.remove("offline");
    presence.classList.add("online");

} else {

    dot.classList.remove("online");
    dot.classList.add("offline");

    presence.textContent = "Offline";
    presence.classList.remove("online");
    presence.classList.add("offline");
}
    document.getElementById("chatUserName").innerText =
        userName;

    const newMessagePanel =
        document.getElementById("newMessagePanel");

    const chatPanel =
        document.getElementById("chatPanel");

    loadMessages(userId);

    if(newMessagePanel.classList.contains("active"))
    {
        chatPanel.style.right = "720px";
    }
    else
    {
        chatPanel.style.right = "380px";
    }

    chatPanel.classList.add("active");

    savePanelState();
}

function closeChatPanel()
{
    document
        .getElementById("chatPanel")
        .classList
        .remove("active");
 savePanelState();
}

function sendMessage()
{
    let text =
        document.getElementById("messageText").value;

    if(text.trim() === "")
    {
        return;
    }

    let formData = new FormData();

    formData.append("receiver_id", activeUser);
    formData.append("message", text);

    fetch("send_message.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(() => {

        document.getElementById("messageText").value = "";

        loadMessages(activeUser);
    });
}

setInterval(function(){

    if(activeUser > 0)
    {
        loadMessages(activeUser);
    }

}, 2000);

</script>

<script>

document.addEventListener("DOMContentLoaded", function(){

    const links = document.querySelectorAll("a");

    links.forEach(link => {

        link.addEventListener("click", function(){

            const href = this.getAttribute("href");

            if(
                href &&
                href !== "#" &&
                !href.startsWith("javascript:")
            ){
                const loader =
                    document.getElementById("pageLoader");

                if(loader){
                    loader.style.display = "flex";
                }
            }

        });

    });

});

window.addEventListener("load", function(){

    const loader =
        document.getElementById("pageLoader");

    if(loader){
        loader.style.display = "none";
    }

});

function savePanelState() {

    localStorage.setItem(
        "messagesPanelOpen",
        document.getElementById("messagesPanel")
            .classList.contains("active")
    );

    localStorage.setItem(
        "newMessagePanelOpen",
        document.getElementById("newMessagePanel")
            .classList.contains("active")
    );

    localStorage.setItem(
        "chatPanelOpen",
        document.getElementById("chatPanel")
            .classList.contains("active")
    );

    localStorage.setItem(
        "activeUser",
        activeUser || 0
    );

    localStorage.setItem(
        "chatUserName",
        document.getElementById("chatUserName").innerText
    );
}
window.addEventListener("DOMContentLoaded", function(){

    const messagesPanel =
        document.getElementById("messagesPanel");

    const newMessagePanel =
        document.getElementById("newMessagePanel");

    const chatPanel =
        document.getElementById("chatPanel");

    messagesPanel.classList.add("no-transition");
    newMessagePanel.classList.add("no-transition");
    chatPanel.classList.add("no-transition");

    if(localStorage.getItem("messagesPanelOpen") === "true")
    {
        messagesPanel.classList.add("active");
    }

    if(localStorage.getItem("newMessagePanelOpen") === "true")
    {
        newMessagePanel.classList.add("active");
    }

    if(localStorage.getItem("chatPanelOpen") === "true")
    {
        chatPanel.classList.add("active");

        chatPanel.style.right =
            localStorage.getItem("newMessagePanelOpen") === "true"
                ? "720px"
                : "380px";

        const userId =
            parseInt(localStorage.getItem("activeUser") || 0);

        const userName =
            localStorage.getItem("chatUserName") || "";

        if(userId > 0)
        {
            activeUser = userId;

            document.getElementById("chatUserName").innerText =
                userName;

            loadMessages(userId);
        }
    }

    setTimeout(() => {

        messagesPanel.classList.remove("no-transition");
        newMessagePanel.classList.remove("no-transition");
        chatPanel.classList.remove("no-transition");

    }, 100);
});
    
function toggleAnnouncements(){

    const panel =
        document.getElementById("announcementsPanel");

    if(panel.style.display === "block")
    {
        panel.style.display = "none";
    }
    else
    {
        panel.style.display = "block";
    }
}


function openAnnouncementForm()
{
    document.getElementById(
        "announcementPopup"
    ).style.display = "flex";
}

function closeAnnouncementForm()
{
    document.getElementById(
        "announcementPopup"
    ).style.display = "none";
}
function saveAnnouncement()
{
    const title =
        document.getElementById("announcementTitle").value;

    const message =
        document.getElementById("announcementMessage").value;

    const formData = new FormData();

    formData.append("title", title);
    formData.append("message", message);

    fetch("save_announcement.php", {

        method: "POST",
        body: formData

    })
    .then(response => response.text())
    .then(() => {

        closeAnnouncementForm();

        location.reload();

    });
}

function toggleProfileMenu(){

    document
        .getElementById('profileMenu')
        .classList
        .toggle('active');
}

/* Close when clicking outside */

document.addEventListener('click', function(e){

    const dropdown =
        document.querySelector('.profile-dropdown');

    const menu =
        document.getElementById('profileMenu');

    if(
        dropdown &&
        !dropdown.contains(e.target)
    ){
        menu.classList.remove('active');
    }
});

</script>

<script>

function openDiptankPopup(){

    document.getElementById(
        "diptankPopup"
    ).style.display = "flex";
}

function closeDiptankPopup(){

    document.getElementById(
        "diptankPopup"
    ).style.display = "none";
}

function openDiptankManagementPopup()
{
    document.getElementById(
        "diptankManagementPopup"
    ).style.display = "flex";
}

function closeDiptankManagementPopup()
{
    document.getElementById(
        "diptankManagementPopup"
    ).style.display = "none";
}

function openAddDiptankPopup()
{
    document.getElementById(
        "addDiptankPopup"
    ).style.display = "flex";
}

function closeAddDiptankPopup()
{
    document.getElementById(
        "addDiptankPopup"
    ).style.display = "none";
}

function saveDiptank()
{
    const form =
        document.getElementById("diptankForm");

    const formData =
        new FormData(form);

    fetch("save_diptank.php", {

        method: "POST",

        body: formData

    })
    .then(response => response.text())
    .then(data => {

        if(data.trim() === "success")
        {
            alert("Diptank saved successfully");

            location.reload();
        }
        else
        {
            alert(data);
        }

    });
}

function toggleProfileMenu(){

    document
        .getElementById('profileMenu')
        .classList
        .toggle('active');
}

/* Close when clicking outside */

document.addEventListener('click', function(e){

    const dropdown =
        document.querySelector('.profile-dropdown');

    const menu =
        document.getElementById('profileMenu');

    if(
        dropdown &&
        !dropdown.contains(e.target)
    ){
        menu.classList.remove('active');
    }
});

function updateUnreadBadge()
{
    fetch("get_unread_count.php")
    .then(response => response.text())
    .then(count => {

        const badge =
            document.getElementById(
                "unreadMessagesBadge"
            );

        if(!badge) return;

        if(parseInt(count) > 0)
        {
            badge.innerText = count;
            badge.style.display = "flex";
        }
        else
        {
            badge.style.display = "none";
        }
    });
}

function refreshConversations()
{
    fetch("get_conversations.php")
    .then(response => response.text())
    .then(data => {

        document.getElementById(
            "messagesList"
        ).innerHTML = data;

    });
}

function updateUnreadBadge()
{
    fetch("get_unread_count.php")
    .then(response => response.text())
    .then(count => {

        const badge =
            document.getElementById(
                "unreadMessagesBadge"
            );

        if(!badge)
        {
            return;
        }

        count = parseInt(count);

        if(count > 0)
        {
            badge.innerText = count;
            badge.style.display = "flex";
        }
        else
        {
            badge.style.display = "none";
        }

    });
}

function updateUnreadMessages() {fetch("get_unread_count.php")
        .then(res => res.text())
        .then(count => {
            const badge = document.getElementById("unreadMessagesBadge");
            count = parseInt(count);

            if (count > 0) {
                badge.style.display = "flex"; // or "block"
                badge.innerText = count;
            } else {
                badge.style.display = "none";
            }
        })
        .catch(err => console.log(err));
}

// run immediately when page loads
updateUnreadMessages();

// keep checking every 3 seconds
setInterval(updateUnreadMessages, 3000);

function openNotifications(){

    document.getElementById(
        "notificationsPopup"
    ).style.display = "flex";

    fetch(
        "mark_notifications_read.php"
    )
    .then(response => response.text())
    .then(data => {

        let badge =
            document.getElementById(
                "notificationBadge"
            );

        if(badge)
        {
            badge.style.display = "none";
        }

    });

}

function closeNotifications(){

    document.getElementById(
        "notificationsPopup"
    ).style.display = "none";

}

function closeUsersPopup() {
    document.getElementById('usersPopup').style.display = 'none';
}
</script>


<script>

function openUsersPopup() {

    const popup = document.getElementById("usersPopup");

    console.log(popup);

    if (popup) {
        popup.style.display = "flex";
    }
}

function closeUsersPopup()
{
    document.getElementById("usersPopup").style.display =
        "none";
}

function openEditUser(
    id,
    username,
    name,
    surname,
    role,
    designation,
    station
){

    document.getElementById("editUserId").value=id;

    document.getElementById("editUsername").value=username;

    document.getElementById("editName").value=name;

    document.getElementById("editSurname").value=surname;

    document.getElementById("editRole").value=role;

    document.getElementById("editDesignation").value=designation;

    document.getElementById("editStation").value=station;

    document.getElementById(
        "editUserPopup"
    ).style.display="flex";
}

function closeEditUser(){

    document.getElementById(
        "editUserPopup"
    ).style.display="none";
}
function closeSuccessPopup(){

    document.getElementById("successPopup").style.display = "none";

    /* Close Edit User popup only */
    document.getElementById("editUserPopup").style.display = "none";
}

function closeErrorPopup(){

    document.getElementById("errorPopup").style.display = "none";

    /* Close Edit User popup only */
    document.getElementById("editUserPopup").style.display = "none";
}

function saveUserChanges(){

    let formData = new FormData();

    formData.append(
        "id",
        document.getElementById("editUserId").value
    );

    formData.append(
        "username",
        document.getElementById("editUsername").value
    );

    formData.append(
        "name",
        document.getElementById("editName").value
    );

    formData.append(
        "surname",
        document.getElementById("editSurname").value
    );

    formData.append(
        "role",
        document.getElementById("editRole").value
    );

    formData.append(
        "designation",
        document.getElementById("editDesignation").value
    );

    formData.append(
        "station",
        document.getElementById("editStation").value
    );

    document.getElementById(
        "savingPopup"
    ).style.display="flex";

    fetch("update_user.php",{

        method:"POST",

        body:formData

    })
    .then(response => response.text())

.then(data=>{

    document.getElementById("savingPopup").style.display = "none";

    if(data.trim() === "success"){

        document.getElementById("successPopup").style.display = "flex";

    }else{

        document.getElementById("errorMessage").innerHTML = data;

        document.getElementById("errorPopup").style.display = "flex";
    }

});

}

function goToApp() {
    window.location.href = "home.php"; // change to your main admin app page
}

function closeEditUserPopup() {

    document.getElementById("editUserPopup").style.display = "none";

}

</script>

<script>
function openRegionTab(evt, regionId)
{
    let panes =
        document.querySelectorAll('.region-pane');

    panes.forEach(function(pane){
        pane.style.display = 'none';
    });

    let tabs =
        document.querySelectorAll('.region-tab');

    tabs.forEach(function(tab){
        tab.classList.remove('active');
    });

    document.getElementById(regionId)
        .style.display = 'block';

    evt.currentTarget.classList.add('active');
}

const IDLE_LIMIT = 5 * 60 * 1000; // 5 minutes

let idleTimer;

function resetIdleTimer()
{
    clearTimeout(idleTimer);

    idleTimer = setTimeout(function ()
    {
        // Close all panels
        document.getElementById("messagesPanel").classList.remove("active");
        document.getElementById("newMessagePanel").classList.remove("active");
        document.getElementById("chatPanel").classList.remove("active");

        // Save closed state
        savePanelState();

        // Log out
        window.location.href = "logout.php";

    }, IDLE_LIMIT);
}

[
    "mousemove",
    "mousedown",
    "keypress",
    "touchstart",
    "scroll",
    "click"
].forEach(function(event)
{
    document.addEventListener(event, resetIdleTimer);
});

resetIdleTimer();

function loadNewMessageUsers()
{
    fetch("load_new_message_users.php")
    .then(response => response.text())
    .then(data => {

        const container = document.getElementById("newMessageUsers");

        if(container){
            container.innerHTML = data;
        }

    });
}

setInterval(function () {

    if (typeof loadDashboardData === "function") {
        loadDashboardData();
    }

    if (typeof loadNotifications === "function") {
        loadNotifications();
    }

    if (typeof loadAnnouncements === "function") {
        loadAnnouncements();
    }

    if (typeof refreshConversations === "function") {
        refreshConversations();
    }

    if (typeof loadNewMessageUsers === "function") {
        loadNewMessageUsers();
    }

    if (typeof loadMessages === "function" && activeUser) {
        loadMessages(activeUser);
    }

    if (typeof updateChatHeaderStatus === "function" && activeUser) {
        updateChatHeaderStatus();
    }

    if (typeof updateUnreadBadge === "function") {
        updateUnreadBadge();
    }

}, 5000);

function filterDamID(value)
{
    value = value.toLowerCase();

    const tables =
        document.querySelectorAll(".records-table tbody");

    tables.forEach(function(tbody){

        tbody.querySelectorAll("tr").forEach(function(row){

            const cells = row.getElementsByTagName("td");

            if(cells.length === 0) return;

            // Dam ID is the 2nd column
            const damID =
                cells[1].innerText.toLowerCase();

            row.style.display =
                damID.indexOf(value) > -1
                ? ""
                : "none";

        });

    });

}

document
.getElementById("damSearch")
.addEventListener("input",function(){

    let v =
    this.value.replace(/\D/g,"");

    if(v.length > 3){

        v =
        v.substring(0,3)
        + "-"
        + v.substring(3,7);

    }

    this.value = v;

});

</script>
</body>
</html>