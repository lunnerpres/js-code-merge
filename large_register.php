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
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Livestock Registers | Registers</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>

        /* =========================================================
    GLOBAL
 ========================================================= */

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Poppins',sans-serif;
        }

        body{
            height:100vh;
            overflow:hidden;

            color:#ffffff;

            background:
                    linear-gradient(
                            135deg,
                            #0f172a 0%,
                            #1d4ed8 45%,
                            #ef4444 100%
                    );
        }

        a{
            text-decoration:none;
            color:inherit;
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

        /* =========================================================
           NAVBAR
        ========================================================= */

       
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


        /* =========================================================
           MAIN LAYOUT
        ========================================================= */

        .container{
            display:flex;
            height:calc(100vh - 80px);
        }

        /* =========================================================
           SIDEBAR
        ========================================================= */

        .sidebar{
    width:260px;

    padding:20px;

    background:rgba(0,0,0,0.15);

    backdrop-filter:blur(12px);

    border-right:1px solid rgba(255,255,255,0.08);
}

       .menu-title{
    color:rgba(255,255,255,0.7);

    font-size:13px;

    margin-bottom:20px;
    padding-left:10px;
}

        /* SIDEBAR LINKS */
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

        /* =========================================================
           CONTENT
        ========================================================= */
.content{
    flex:1;
    padding:20px;
    overflow:hidden;
    position:relative;
}

        /* =========================================================
           MAIN CARD
        ========================================================= */

        .main-card{
    width:100%;
    height:100%;

    display:flex;
    flex-direction:column;

    overflow:hidden;

    border-radius:24px;

    background:rgba(255,255,255,0.08);

    backdrop-filter:blur(18px);

    border:1px solid rgba(255,255,255,0.08);

    box-shadow:0 10px 25px rgba(0,0,0,0.25);
}
        /* =========================================================
           TAB HEADER
        ========================================================= */

        .tab-header{
            width:100%;

            display:flex;
            align-items:center;

            overflow-x:auto;
            overflow-y:hidden;

            white-space:nowrap;

            background:rgba(0,0,0,0.22);

            border-bottom:1px solid rgba(255,255,255,0.08);
        }

        .tab-header::-webkit-scrollbar{
            height:4px;
        }

        .tab-header::-webkit-scrollbar-thumb{
            background:rgba(255,255,255,0.18);
            border-radius:20px;
        }

        /* TAB BUTTONS */

        .tab-btn{
            border:none;
            outline:none;

            cursor:pointer;

            padding:14px 18px;

            min-width:max-content;

            font-size:12px;
            font-weight:600;

            color:rgba(255,255,255,0.82);

            background:transparent;

            border-right:1px solid rgba(255,255,255,0.08);

            transition:0.25s;
        }

        .tab-btn:hover{
            background:rgba(255,255,255,0.08);
            color:#ffffff;
        }

        .tab-btn.active{
            background:#ffffff;
            color:#1e293b;
        }

        /* =========================================================
           TAB CONTENT
        ========================================================= */

        .tab-content{
            display:none;
            width:100%;
            height:100%;
            padding:20px;
            overflow:hidden;
        }

        .tab-content.active{
            display:block;
        }

        /* =========================================================
           MAIN LAYOUT
        ========================================================= */

        .main-layout{
            width:100%;
            height:100%;

            display:flex;
            gap:10px;

            align-items:flex-start;

            overflow:hidden;
        }

        /* =========================================================
           LEFT CONTENT
        ========================================================= */

        .left-content{
            flex:1;

            display:flex;
            flex-direction:column;

            gap:8px;

            min-width:0;

            overflow:hidden;
        }

        /* =========================================================
           INPUT TABLE WRAPPER
        ========================================================= */

.excel-wrapper{
    flex: 1;
    min-height: 0;
    overflow-y: auto;
}

        /* =========================================================
           INPUT TABLE SECTION
        ========================================================= */

        .excel-table-section{
            width:100%;

            padding:5px;

            border-radius:6px;

            background:#f8fafc;

            border:1px solid #cbd5e1;
        }

        /* =========================================================
           INPUT TABLE
        ========================================================= */

        .excel-table{
            width:100%;

            border-collapse:collapse;

            table-layout:fixed;

            font-size:11px;
        }

        .excel-table td{
            height:28px;

            padding:3px;

            text-align:center;

            color:#111827;

            background:#ffffff;

            border:1px solid #d1d5db;

            font-weight:600;
        }

        /* HEADER ROW */

        .head-row td{
            background:#e5e7eb;
            font-weight:700;
        }

        /* SUMMARY ROW */

        .summary-row td{
            background:#f3f4f6;
            font-weight:700;
        }

        /* =========================================================
           INPUTS
        ========================================================= */

        .excel-table input{
            width:100%;
            height:22px;

            padding:0 4px;

            border-radius:3px;
            outline:none;

            text-align:center;

            font-size:11px;

            color:#111827;

            border:1px solid #94a3b8;

            background:
                    linear-gradient(
                            to bottom,
                            #ffffff,
                            #e5e7eb
                    );

            box-shadow:
                    inset 0 1px 2px rgba(255,255,255,0.9),
                    inset 0 -1px 2px rgba(0,0,0,0.08);
        }

        /* =========================================================
           FUNCTION PANEL
        ========================================================= */

        .function-panel{
            width:145px;

            flex-shrink:0;

            padding:8px;

            border-radius:6px;

            background:#f8fafc;

            border:1px solid #cbd5e1;

            display:flex;
            flex-direction:column;

            gap:8px;
        }

        /* FUNCTION TITLE */

        .function-title{
            text-align:center;

            font-size:11px;
            font-weight:700;

            color:#1e293b;
        }

      

        /* =========================================================
           RECORDS WRAPPER
        ========================================================= */

.records-wrapper{
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
}

        /* =========================================================
           RECORDS TOPBAR
        ========================================================= */

        .records-topbar{
            padding:5px 6px;
            border-bottom:1px solid #cbd5e1;
            background:#eef2ff;
    margin:0; 
        }

        /* SEARCH FORM */

        .records-search{
            display:flex;
            align-items:center;

            gap:6px;

            flex-wrap:wrap;
        }

        /* SEARCH INPUTS */

        .records-search input{
            height:26px;

            padding:0 6px;

            border-radius:3px;
            outline:none;

            font-size:11px;

            color:#111827;

            border:1px solid #94a3b8;

            background:
                    linear-gradient(
                            to bottom,
                            #ffffff,
                            #e5e7eb
                    );
        }

        /* SEARCH BUTTONS */

        .records-btn{
            height:26px;

            padding:0 10px;

            border-radius:3px;

            border:1px solid #94a3b8;

            cursor:pointer;

            font-size:11px;
            font-weight:600;

            color:#1e293b;

            background:
                    linear-gradient(
                            to bottom,
                            #ffffff,
                            #dbeafe
                    );
        }

        .records-btn:hover{
            background:
                    linear-gradient(
                            to bottom,
                            #eff6ff,
                            #bfdbfe
                    );
        }

        /* =========================================================
           RECORDS TABLE WRAPPER
        ========================================================= */

.records-table-wrapper{
    flex: 1;
    min-height: 0;
    display: flex;
    overflow: hidden;
}

        /* =========================================================
           RECORDS TABLE
        ========================================================= */

        .records-table{
            width:100%;

            border-collapse:collapse;

            font-size:10px;

            background:#ffffff;
        }

        /* FIXED HEADER */

        .records-table thead{
            position:sticky;
            top:0;
            z-index:10;
        }

        /* TABLE HEADERS */

        .records-table th{
            position:sticky;
            top:0;

            padding:4px 5px;

            white-space:nowrap;

            font-size:10px;
            font-weight:700;

            color:#1e293b;

            border:1px solid #cbd5e1;

            background:
                    linear-gradient(
                            to bottom,
                            #f8fafc,
                            #dbeafe
                    );

            box-shadow:
                    inset 0 1px 0 #ffffff,
                    0 1px 2px rgba(0,0,0,0.08);
        }

        /* TABLE CELLS */

        .records-table td{
            padding:3px 4px;

            text-align:center;

            white-space:nowrap;

            color:#111827;

            border:1px solid #e5e7eb;

            background:#ffffff;
        }

        /* ROW HOVER */

        .records-table tbody tr:hover td{
            background:#eff6ff;
        }

        /* STRIPED ROWS */

        .records-table tbody tr:nth-child(even) td{
            background:#f8fafc;
        }

        /* =========================================================
           DELETE BUTTON
        ========================================================= */

        .delete-btn{
            display:inline-block;

            padding:2px 6px;

            border-radius:3px;

            font-size:10px;
            font-weight:600;

            color:#991b1b;

            background:#fee2e2;

            border:1px solid #fca5a5;
        }

        /* =========================================================
           NO DATA
        ========================================================= */

        .no-data{
            padding:12px !important;

            text-align:center;

            color:#64748b;

            font-size:11px;
        }

        /* =========================================================
           SCROLLBAR
        ========================================================= */

        .records-table-wrapper::-webkit-scrollbar,
        .tab-content::-webkit-scrollbar{
            width:6px;
            height:6px;
        }

        .records-table-wrapper::-webkit-scrollbar-thumb,
        .tab-content::-webkit-scrollbar-thumb{
            background:#94a3b8;
            border-radius:20px;
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
        /* =========================================================
           MOBILE
        ========================================================= */

@media(max-width:1000px){

    .sidebar{
        width:85px;
    }

    .sidebar a span{
        display:none;
    }

    .main-layout{
        flex-direction:column;
    }

    .function-panel{
        width:100%;
        flex-direction:row;
        flex-wrap:wrap;
    }

    .function-btn{
        flex:1 1 45%;
    }

    .records-search{
        flex-direction:column;
        align-items:stretch;
    }
}
        .excel-table select{
            width:100%;
            height:22px;

            padding:0 4px;

            border-radius:3px;
            outline:none;

            text-align:center;

            font-size:11px;

            color:#111827;

            border:1px solid #94a3b8;

            background:
                    linear-gradient(
                            to bottom,
                            #ffffff,
                            #e5e7eb
                    );

            box-shadow:
                    inset 0 1px 2px rgba(255,255,255,0.9),
                    inset 0 -1px 2px rgba(0,0,0,0.08);
        }
        /* OVERLAY */


        /* HEADER */

        .popup-header{
            padding:12px 16px;
            font-size:14px;
            font-weight:700;
            color:#ffffff;
        }

        .popup-success .popup-header{
            background:linear-gradient(to bottom,#2f855a,#276749);
        }

        .popup-error .popup-header{
            background:linear-gradient(to bottom,#c53030,#9b2c2c);
        }

        .popup-loading .popup-header{
            background:linear-gradient(to bottom,#2563eb,#1d4ed8);
        }

        /* BODY */

        .popup-body{
            padding:24px 18px;
            text-align:center;
            color:#1e293b;
            font-size:13px;
            background:#f8fafc;
        }

        /* LOADER */

        .loader{
            width:46px;
            height:46px;
            margin:0 auto 14px auto;

            border:4px solid #dbeafe;
            border-top:4px solid #2563eb;
            border-radius:50%;

            animation:spin 1s linear infinite;
        }

        @keyframes spin{
            100%{
                transform:rotate(360deg);
            }
        }

        /* ICONS */

        .popup-icon{
            font-size:48px;
            margin-bottom:12px;
        }

        .success-icon{
            color:#16a34a;
        }

        .error-icon{
            color:#dc2626;
        }

        /* FOOTER */

        .popup-footer{
            padding:12px;
            background:#eef2f7;
            text-align:right;
            border-top:1px solid #dbeafe;
        }

        .popup-btn{
            height:34px;
            padding:0 16px;
            border:none;
            border-radius:4px;
            cursor:pointer;

            font-size:12px;
            font-weight:600;

            color:#ffffff;

            background:linear-gradient(to bottom,#2563eb,#1d4ed8);
        }
.animal-popup{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.65);
    z-index:99999;
    justify-content:center;
    align-items:center;
}

.animal-popup-content{
    width:500px;
    max-width:95%;
    background:#fff;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,.3);
}

.animal-popup-header{
    background:#1e40af;
    color:white;
    padding:15px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.animal-popup-header span{
    cursor:pointer;
    font-size:28px;
}

.animal-details{
    padding:20px;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
    font-size:14px;
}

.animal-buttons{
    display:flex;
    gap:10px;
    justify-content:center;
    padding:20px;
    border-top:1px solid #e5e7eb;
}

.animal-buttons a{
    text-decoration:none;
    padding:10px 20px;
}
.animal-popup-content{
    background:#ffffff;
    color:#111827;
}

.animal-details{
    color:#111827;
}

.animal-details div{
    color:#111827;
    font-size:14px;
    padding:6px 0;
}

.animal-details span{
    color:#000000;
    font-weight:600;
}

.animal-details strong{
    color:#1e293b;
}.profile-popup-content{
    background:#ffffff;
    color:#333333;
    width:500px;
    max-width:90%;
    border-radius:8px;
    padding:15px;
    font-family:Arial, sans-serif;
    font-size:13px;
    font-weight:normal;
}

.profile-popup-content *{
    color:#333333 !important;
    font-weight:normal !important;
}
.profile-header{
    background:#f8f9fa;
    border-bottom:1px solid #dcdcdc;
    padding:8px;
    font-size:14px;
    font-weight:600;
    margin-bottom:10px;
}

.profile-table{
    width:100%;
    border-collapse:collapse;
    font-size:12px;
}

.profile-table td{
    border:1px solid #dcdcdc;
    padding:6px;
}

.profile-table td:first-child{
    background:#f5f5f5;
    width:35%;
}

.profile-buttons{
    margin-top:12px;
    text-align:right;
}

.profile-buttons button{
    padding:5px 10px;
    font-size:12px;
    margin-left:4px;
    cursor:pointer;
}
#editAnimalPopup{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.5);
    z-index:100000;
    justify-content:center;
    align-items:center;
}
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
.table-container{
    flex: 1;
    min-height: 0;

    display: flex;
    flex-direction: column;

    position: relative;
    overflow: hidden;
}

.expanded-header {
    display: none;
}

.table-container.expanded {
    position: fixed;
    top: 80px; /* Header height */
    left: 10px;
    right: 10px;
    bottom: 10px;

    background: #fff;
    z-index: 9998;

    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);

    overflow: auto;
}

.expanded-header {
    display: none;
}

.table-container.expanded .expanded-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    position: sticky;
    top: 0;

    padding: 12px 18px;

    background: linear-gradient(
        135deg,
        #dff4ff 0%,
        #b8e6ff 45%,
        #8fd3ff 100%
    );

    color: #000;

    border-bottom: 1px solid #7fc8f8;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.8),
        0 2px 6px rgba(0,0,0,0.15);

    z-index: 10;
}
.close-expanded {
    width: 28px;
    height: 28px;

    border: none;
    border-radius: 50%;

    background: linear-gradient(
        180deg,
        #ffffff,
        #d9ecff
    );

    color: #000;

    font-size: 14px;
    font-weight: bold;

    cursor: pointer;

    box-shadow:
        0 2px 5px rgba(0,0,0,0.15),
        inset 0 1px 0 rgba(255,255,255,0.9);

    transition: all 0.2s ease;
}

.close-expanded:hover {
    transform: scale(1.05);

    background: linear-gradient(
        180deg,
        #f5fbff,
        #cde6ff
    );
}

.close-expanded:active {
    transform: scale(0.95);
}
.expanded-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #000;
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

    width:38px;
    height:38px;

    border-radius:50%;

    background:rgba(255,255,255,.12);

    display:flex;
    align-items:center;
    justify-content:center;

    flex-shrink:0;
}

.message-avatar i{

    font-size:18px;

    color:rgba(255,255,255,.85);
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

    width:40px;
    height:40px;

    border-radius:50%;

    background:rgba(255,255,255,.12);

    display:flex;
    align-items:center;
    justify-content:center;

    flex-shrink:0;
}

.user-avatar i{

    font-size:18px;
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
.tool-btn{
    width:100%;
    height:34px;

    border:none;
    border-radius:6px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #ff3b5c
        );

    color:white;

    font-size:12px;
    font-weight:600;

    cursor:pointer;

    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;

    transition:.2s;
}

.tool-btn:hover{
    transform:translateY(-2px);

    box-shadow:
        0 5px 12px rgba(0,0,0,.25);
}

.danger-btn{
    background:
        linear-gradient(
            135deg,
            #dc2626,
            #ef4444
        );
}


.side-actions{
    width:130px;

    display:flex;
    flex-direction:column;

    gap:8px;

    margin-top:-10px;     /* aligns with report form */
    margin-right:20px;   /* keeps away from screen edge */

    flex-shrink:0;
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
/* Overlay */
#transferPopup {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.35);
    justify-content: center;
    align-items: center;
    z-index: 10000;
}

/* Main Popup */
.transfer-popup {
    width: 420px;
    max-width: 95%;
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;

    box-shadow:
        0 15px 40px rgba(0,0,0,0.15),
        0 2px 8px rgba(0,0,0,0.08);

    animation: popupFade 0.25s ease;
}

/* Header */
.transfer-header {
    background: linear-gradient(
        to bottom,
        #fafafa,
        #eeeeee
    );

    color: #222;
    font-size: 18px;
    font-weight: 600;

    padding: 18px 22px;

    border-bottom: 1px solid #e2e2e2;
}

/* Body */
.transfer-body {
    padding: 24px;
}

.transfer-group {
    margin-bottom: 18px;
}

.transfer-group label {
    display: block;
    margin-bottom: 8px;

    color: #333;
    font-size: 14px;
    font-weight: 500;
}

.transfer-group input {
    width: 100%;
    box-sizing: border-box;

    padding: 12px 14px;

    border: 1px solid #d6d6d6;
    border-radius: 8px;

    background: #fff;
    color: #222;

    font-size: 14px;

    transition: all .2s ease;
}

.transfer-group input:focus {
    outline: none;

    border-color: #bdbdbd;

    box-shadow:
        0 0 0 3px rgba(0,0,0,0.05);
}

/* Footer */
.transfer-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;

    padding: 18px 24px;

    background: #f7f7f7;
    border-top: 1px solid #e5e5e5;
}

/* Buttons */
.transfer-btn {
    min-width: 100px;

    padding: 10px 18px;

    border: none;
    border-radius: 8px;

    font-size: 14px;
    font-weight: 600;

    cursor: pointer;
    transition: all .2s ease;
}

.cancel-btn {
    background: #e8e8e8;
    color: #333;
}

.cancel-btn:hover {
    background: #dcdcdc;
}

.confirm-btn {
    background: #4f4f4f;
    color: #fff;
}

.confirm-btn:hover {
    background: #3d3d3d;
}

/* Animation */
@keyframes popupFade {
    from {
        opacity: 0;
        transform: translateY(-15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
/* ERP STYLE POPUP */

#recordPopup{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.55);
    justify-content:center;
    align-items:center;
    z-index:9999;
}

/* MAIN WINDOW */

.record-popup-box{

    width:900px;
    max-width:95vw;

    background:#e9edf2;

    border:1px solid #9aa7b5;

    border-radius:8px;

    box-shadow:
        0 10px 30px rgba(0,0,0,.45),
        inset 0 1px 0 #ffffff;

    overflow:hidden;
}

/* HEADER */

.record-popup-box .popup-header{

    height:42px;

    background:
        linear-gradient(
            to bottom,
            #4f8df5,
            #2f6fd8
        );

    color:#fff;

    display:flex;
    align-items:center;
    justify-content:space-between;

    padding:0 15px;

    border-bottom:1px solid #2b5eb4;
}

.record-popup-box .popup-header h2{

    font-size:15px;
    font-weight:600;

    margin:0;
}

.record-popup-box .popup-header button{

    background:none;
    border:none;

    color:white;

    font-size:18px;

    cursor:pointer;
}

/* BODY */

.record-popup-content{

    padding:12px;
}

/* TABLE */

.excel-table{

    width:100%;
    border-collapse:collapse;

    background:white;

    font-size:12px;

    color:#222;
}

.excel-table td{

    border:1px solid #c7d1db;

    padding:5px;
}

/* HEADER ROW */

.head-row td{

    background:
        linear-gradient(
            to bottom,
            #f7f9fb,
            #d9e3ee
        );

    font-weight:600;

    color:#0b4ea2;
}

/* INPUTS */

.excel-table input{

    width:100%;

    height:28px;

    border:1px solid #b6c2ce;

    background:#ffffff;

    padding:4px 6px;

    font-size:12px;

    border-radius:3px;
}

.excel-table input:focus{

    border-color:#4f8df5;

    outline:none;

    box-shadow:0 0 4px rgba(79,141,245,.4);
}

/* SUMMARY ROW */

.summary-row td{

    background:
        linear-gradient(
            to bottom,
            #eef3f8,
            #d7e2ee
        );

    font-weight:bold;
}

/* BUTTONS */

.record-actions{

    margin-top:12px;

    display:flex;
    gap:10px;
}

.record-btn{

    min-width:120px;

    height:34px;

    border:1px solid #7e96b3;

    background:
        linear-gradient(
            to bottom,
            #ffffff,
            #d9e3ee
        );

    color:#1d3557;

    font-weight:600;

    cursor:pointer;

    border-radius:4px;
}

.record-btn:hover{

    background:
        linear-gradient(
            to bottom,
            #ffffff,
            #c5d4e5
        );
}

.inner-popup{
    position:absolute;
    top:0;
    left:0;
    right:0;
    bottom:0;

    display:flex;
    justify-content:center;
    align-items:center;

    background:rgba(0,0,0,0.65);

    z-index:999999;

    border-radius:20px;
}

.inner-popup-box{

    width:350px;

    background:linear-gradient(
        145deg,
        #334155,
        #1e293b
    );

    border:1px solid #64748b;

    border-radius:18px;

    padding:25px;

    text-align:center;

    box-shadow:
        0 15px 35px rgba(0,0,0,0.5);

    color:white;
}
.table-actions{

    display:flex;

    justify-content:flex-end;

    margin:10px 0;

}

.create-record-btn{

    display:flex;
    align-items:center;
    gap:8px;

    padding:8px 16px;

    border:none;
    border-radius:10px;

    cursor:pointer;

    font-size:13px;
    font-weight:600;

    color:#fff;

    background:
        linear-gradient(
            145deg,
            #38bdf8,
            #0284c7
        );

    box-shadow:
        0 4px 12px rgba(2,132,199,0.35);

    transition:all 0.25s ease;
}

.create-record-btn i{

    font-size:16px;

    color:#ffffff;
}

.create-record-btn:hover{

    transform:translateY(-2px);

    background:
        linear-gradient(
            145deg,
            #0ea5e9,
            #0369a1
        );

    box-shadow:
        0 8px 18px rgba(2,132,199,0.45);
}

.create-record-btn:active{

    transform:translateY(0);
}


.records-popup{

    width:95%;
    max-width:1300px;

    max-height:90vh;

    overflow:auto;

    background:#fff;

    border-radius:10px;

    box-shadow:0 0 25px rgba(0,0,0,.3);
}
#dailyRecordsOverlay{

    display:none;

    position:fixed;

    top:0;
    left:0;

    width:100%;
    height:100%;

    background:rgba(0,0,0,.5);

    z-index:99999;
}

/* Search Results Popup */
#dailyRecordsResults{

    display:none;

    position:fixed;

    top:50%;
    left:50%;

    transform:translate(-50%, -50%);

    width:85%;
    max-width:1100px;

    max-height:75vh;

    background:#f8fafc;

    border-radius:10px;

    box-shadow:0 8px 30px rgba(0,0,0,.25);

    border:1px solid #cbd5e1;

    z-index:99999;

    overflow:hidden;
}


/* Table container */
#dailyRecordsTableContainer{

    padding:8px;

    max-height:65vh;

    overflow:auto;

    background:#f1f5f9;
}

/* Compact phpMyAdmin style table */
.phpmyadmin-table{

    width:100%;

    border-collapse:collapse;

    font-size:10px;

    background:white;

    table-layout:fixed;
}

.phpmyadmin-table th{

    background:linear-gradient(
        to bottom,
        #bae6fd,
        #7dd3fc
    );

    color:#0f172a;

    padding:6px 4px;

    border:1px solid #93c5fd;

    white-space:nowrap;
}

.phpmyadmin-table td{

    padding:5px 4px;

    border:1px solid #dbeafe;

    text-align:center;

    white-space:nowrap;
}

.phpmyadmin-table tbody tr:nth-child(even){

    background:#f8fafc;
}

.phpmyadmin-table tbody tr:hover{

    background:#e0f2fe;
}

/* Close button */
.close-expanded{

    width:28px;

    height:28px;

    border:none;

    border-radius:50%;

    background:white;

    cursor:pointer;

    font-size:14px;

    font-weight:bold;
}

/* Scrollable table */
#dailyRecordsTableContainer{

    max-height:500px;

    overflow:auto;

    padding:10px;

    background:#f1f5f9;
}
.table-responsive{
    overflow-x:auto;
}
.search-expanded-header{

    background:linear-gradient(
        to bottom,
        #7dd3fc,
        #38bdf8
    );

    color:#0f172a;

    padding:10px 15px;

    display:flex;

    justify-content:space-between;

    align-items:center;

    border-bottom:1px solid #93c5fd;
}

.search-expanded-header h3{

    margin:0;

    font-size:15px;

    font-weight:600;
}

.close-search-expanded{

    border:none;

    background:white;

    width:28px;
    height:28px;

    border-radius:50%;

    cursor:pointer;

    font-weight:bold;
}
.header-actions{

    display:flex;

    align-items:center;

    gap:8px;
}
#births .birth-select-col {
    display: none;
}
#permitsin .permitin-select-col {
    display: none;
}
#cattle .cattle-select-col {
    display: none;
}

.header-icon-btn{

    width:32px;
    height:32px;

    border:none;

    border-radius:6px;

    background:white;

    cursor:pointer;

    color:#0284c7;

    font-size:14px;

    transition:.2s;
}

.header-icon-btn:hover{

    background:#e0f2fe;

    transform:translateY(-1px);
}

.fa-file-pdf{
    color:#dc2626;
}

.fa-file-excel{
    color:#16a34a;
}
#dailyRecordsResults table,
#dailyRecordsResults td,
#dailyRecordsResults th,
#dailyRecordsResults h3 {
    color: #000 !important;
}
#birthPopup .record-popup-box{

    width:900px;

    max-width:95%;

    max-height:85vh;

    overflow:auto;
}
.select-col {
    display: none;
    text-align: center;
}

.records-wrapper .select-col{
    display:none;
}

.records-wrapper.delete-mode .select-col{
    display:table-cell;
}

/* === DELETE BAR (Floating phpMyAdmin inspired) === */
.delete-bar{
    position: absolute;
    top: 0;
    left: 0;
    right: 0;

    z-index: 9999;

    display: flex;

    justify-content: space-between;
    align-items: center;

    padding: 6px 12px;

    background: linear-gradient(135deg,#ffe6cc,#ffd1a8,#ffbf80);

    border-bottom: 1px solid rgba(0,0,0,0.12);
    box-shadow: 0 1px 6px rgba(0,0,0,0.15);

    font-size: 12px;

    /* IMPORTANT */
    height: 40px;

    transform: translateY(-120%);
    transition: .25s ease;

    visibility: hidden;
    opacity: 0;
}

/* Show state */
.delete-bar.show{

    opacity: 1;
    visibility: visible;
    transform: translateY(0);

}

/* MARK ALL */
.mark-all {
    font-weight: 500;
    font-size: 12px;
    color: #5a3a1a;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* ACTIONS */
.actions {
    display: flex;
    gap: 6px;
}

/* === COMPACT 3D BUTTONS === */
.btn-3d {
    border: none;
    padding: 5px 10px;   /* reduced size */
    border-radius: 6px;
    cursor: pointer;

    font-size: 11px;     /* smaller text */
    font-weight: 600;

    transition: all 0.12s ease-in-out;

    box-shadow:
        0 3px 0 rgba(0,0,0,0.18),
        0 4px 8px rgba(0,0,0,0.12);

    transform: translateY(0);
}

/* press effect */
.btn-3d:active {
    transform: translateY(2px);
    box-shadow:
        0 1px 0 rgba(0,0,0,0.18),
        0 2px 5px rgba(0,0,0,0.12);
}

/* danger button */
.btn-3d.danger {
    background: linear-gradient(145deg, #ff6b6b, #ff3b3b);
    color: white;
}

/* neutral button */
.btn-3d.neutral {
    background: linear-gradient(145deg, #f3f3f3, #d6d6d6);
    color: #333;
}

/* checkbox smaller */
.delete-bar input[type="checkbox"] {
    transform: scale(0.95);
    cursor: pointer;
}
.warning-icon{
    background:#fff3cd;
    color:#856404;
    border:2px solid #ffeeba;
}
.success-icon{
    background:#e8f5e9;
    color:#2e7d32;
    border:2px solid #81c784;
}
/* ===== FATE POPUP ===== */

.fate-popup{
    position:fixed;
    inset:0;
    display:none;
    justify-content:center;
    align-items:center;
    background:rgba(0,0,0,.25);
    z-index:9999;
}

.fate-popup-box{

    width:700px;
    max-width:90%;
    max-height:75vh;

    background:#f5f5f5;

    border:1px solid #9ea7b3;

    border-radius:6px;

    overflow:hidden;

    box-shadow:
        0 10px 25px rgba(0,0,0,.25);
}

/* ===== HEADER ===== */

.fate-header{

    display:flex;
    justify-content:space-between;
    align-items:center;

    padding:8px 10px;

    background:linear-gradient(
        to bottom,
        #dfe8f2,
        #c7d3e1
    );

    border-bottom:1px solid #aab7c6;
}

.fate-title{

    font-size:14px;
    font-weight:normal;
    color:#2c3e50;

    display:flex;
    align-items:center;
    gap:6px;
}

/* ===== SEARCH AREA ===== */

.fate-header-actions{

    display:flex;
    align-items:center;
    gap:5px;
}

.fate-search-input{

    width:90px;
    height:24px;

    padding:2px 6px;

    border:1px solid #9aa7b5;

    font-size:11px;

    color:#000;
}

.fate-search-btn{

    height:26px;

    padding:0 8px;

    border:1px solid #8d9ba8;

    background:linear-gradient(
        to bottom,
        #ffffff,
        #d9e1ea
    );

    cursor:pointer;

    font-size:11px;

    color:#000;
}

.fate-search-btn:hover{
    background:#eef4fa;
}

/* ===== CLOSE BUTTON ===== */

.fate-close-btn{

    width:26px;
    height:26px;

    border:1px solid #8d9ba8;

    background:linear-gradient(
        to bottom,
        #ffffff,
        #d9e1ea
    );

    cursor:pointer;

    color:#000;
}

.fate-close-btn:hover{
    background:#eef4fa;
}

/* ===== CONTENT ===== */

.fate-content{

    padding:10px;

    background:#ececec;
}

/* ===== TABLE ===== */

.fate-table{

    width:100%;

    border-collapse:collapse;

    background:white;

    font-size:11px;
}

.fate-table th{

    background:#dbe3ec;

    border:1px solid #c1c9d3;

    padding:5px 8px;

    color:#2f3b4a;

    font-weight:normal;

    text-align:center;
}

.fate-table td{

    border:1px solid #d0d6dd;

    padding:5px 8px;

    color:#000;

    text-align:center;
}

/* Excel-like hover */

.fate-table tbody tr:hover{

    background:#eef6ff;
}

/* Zebra */

.fate-table tbody tr:nth-child(even){

    background:#f7f7f7;
}

/* No data */

.no-data{

    text-align:center;

    padding:15px;

    color:#555;
}

/* Scroll */

.records-table-wrapper{

    max-height:450px;

    overflow-y:auto;
}
/* ==============================
   DAILY RECORD POPUP (UPGRADED 3D STYLE)
   ============================== */

.popup-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.25);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:9999;
}

/* SMALLER + MORE 3D POPUP */
.record-popup-box{

    width:750px;              /* smaller than 900px */
    max-width:92%;
    max-height:88vh;

    background:linear-gradient(#f9f9f9, #e9eef3);

    border:1px solid #8ea0b3;
    border-radius:10px;

    overflow:hidden;

    /* STRONGER 3D SHADOW */
    box-shadow:
        0 18px 35px rgba(0,0,0,.35),
        inset 0 1px 0 rgba(255,255,255,.7);

    transform: translateY(0);
}

/* HEADER - LIGHT SKY BLUE */
.record-popup-box .popup-header{

    display:flex;
    justify-content:space-between;
    align-items:center;

    padding:10px 12px;

    background:linear-gradient(
        to bottom,
        #87cefa,   /* sky blue */
        #bfe6ff
    );

    border-bottom:1px solid #6fb6e6;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.6);
}

.record-popup-box .popup-header h2{

    margin:0;

    font-size:14px;
    font-weight:600;
    color:#0f2f44;
    text-shadow:0 1px 0 rgba(255,255,255,.4);
}

.record-popup-box .popup-header button{

    width:28px;
    height:28px;

    border:1px solid #5a9dcf;

    background:linear-gradient(#ffffff, #d9efff);

    cursor:pointer;

    color:#0f2f44;

    border-radius:4px;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.8),
        0 2px 4px rgba(0,0,0,.15);
}

.record-popup-box .popup-header button:hover{
    background:#e6f6ff;
}

/* CONTENT */
.record-popup-content{
    padding:10px;
    background:#eef2f6;
}

/* TABLE */
.excel-table{

    width:100%;
    border-collapse:collapse;
    background:#fff;

    font-size:11px;

    box-shadow: inset 0 0 10px rgba(0,0,0,.03);
}

.excel-table td{

    border:1px solid #cfd6dd;

    padding:4px 6px;

    color:#000;
}

/* LABEL CELLS */
.excel-table td:nth-child(odd){

    background:#dbe3ec;

    font-weight:normal;

    width:120px;
}

/* HEADER ROW */
.head-row td{
    background:#d5e3f0 !important;
    font-weight:600;
}

/* SUMMARY ROW */
.summary-row td{
    background:#eef4fa;
}

/* INPUTS */
.excel-table input,
.excel-table select{

    width:100%;
    height:24px;

    border:1px solid #b7c2cd;

    background:#fff;

    padding:2px 5px;

    font-size:11px;

    box-sizing:border-box;

    border-radius:3px;
}

/* FOCUS EFFECT */
.excel-table input:focus,
.excel-table select:focus{

    outline:none;

    border-color:#4aa3df;

    box-shadow:0 0 3px rgba(74,163,223,.4);
}

/* ==============================
   IMPORTANT INPUTS (RED BORDER)
   Add class="important-field"
   ============================== */
.excel-table input.important-field,
.excel-table select.important-field{
    border:1.5px solid #e74c3c;
    background:#fff5f5;
}

/* ==============================
   ANIMAL DETAILS (YELLOW BORDER)
   Wrap section OR add class
   ============================== */
.excel-table .animal-field,
.excel-table td.animal-field input,
.excel-table td.animal-field select{
    border:1.5px solid #f1c40f !important;
    background:#fffdf2;
}

/* BUTTON AREA */
.record-actions{

    display:flex;
    justify-content:flex-end;

    gap:6px;

    padding-top:10px;
}

/* BUTTONS */
.record-btn{

    min-width:80px;
    height:28px;

    border:1px solid #7f9bb3;

    background:linear-gradient(#ffffff, #d6e6f5);

    color:#000;

    cursor:pointer;

    font-size:11px;

    border-radius:4px;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.8),
        0 2px 4px rgba(0,0,0,.15);
}

.record-btn:hover{
    background:linear-gradient(#ffffff, #cfe3f5);
}

.record-btn:active{
    transform:translateY(1px);
}
/* ==============================
   COMPUTING INPUTS (BLUE)
   ============================== */
.excel-table input.computing-field,
.excel-table select.computing-field{
    border:1.5px solid #3498db;
    background:#f0f8ff;
    box-shadow:0 0 3px rgba(52,152,219,.25);
}

/* ==============================
   READ ONLY INPUTS (GREEN)
   ============================== */
.excel-table input[readonly],
.excel-table select[readonly]{
    border:1.5px solid #2ecc71;
    background:#f3fff6;
    color:#1e7a3b;
}

/* optional stronger visual lock */
.excel-table input[readonly]{
    cursor:not-allowed;
}
/* ==============================
   TAG DROPDOWN CONTAINER
   ============================== */
.tag-dropdown{
    position:relative;
    display:inline-block;
}

/* ==============================
   DROPDOWN MENU
   ============================== */
.tag-menu{
    position:absolute;
    top:42px;
    left:0;

    width:180px;

    background:linear-gradient(#ffffff, #eef4fa);
    border:1px solid #8ea0b3;
    border-radius:8px;

    box-shadow:
        0 10px 25px rgba(0,0,0,.25),
        inset 0 1px 0 rgba(255,255,255,.7);

    display:none;
    flex-direction:column;

    overflow:hidden;
    z-index:999;
}

/* SHOW STATE */
.tag-menu.show{
    display:flex;
}

/* ==============================
   MENU ITEMS
   ============================== */
.tag-item{
    padding:8px 10px;

    font-size:12px;
    text-align:left;

    border:none;
    background:transparent;

    cursor:pointer;

    color:#1f2d3a;
    border-bottom:1px solid #d6e3ef;
}

.tag-item:hover{
    background:#d9ecff;
}

/* last item no border */
.tag-item:last-child{
    border-bottom:none;
}
.popup-overlay
{
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.35);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 999999; /* 🔥 MUST BE HIGH */
}
#errorPoup
{
    z-index: 999999 !important;
}
/* base popups */
.popup-overlay
{
    z-index: 10000;
}

/* form popups */
.form-popup
{
    z-index: 20000;
}

/* error popup (highest priority) */
#errorPoup
{
    z-index: 90000;
}

/* loading popup (always top of everything) */
#loadingPoup
{
    z-index: 100000;
}
.record-actions{
    display:flex;
    justify-content:center;
    align-items:center;
    padding:15px;
    background:#e8edf4;
    border-top:1px solid #c8d0da;
}

.record-btn{
    min-width:180px;
    height:40px;
    border:none;
    border-radius:6px;
    background:linear-gradient(to bottom,#4da3ff,#2c7be5);
    color:#fff;
    font-weight:600;
    cursor:pointer;
    box-shadow:0 3px 8px rgba(0,0,0,.2);
    transition:.2s;
}

.record-btn:hover{
    transform:translateY(-1px);
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
#deathSettingsMenu .tag-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    width:100%;
}

#deathSettingsMenu .menu-arrow{
    font-weight:bold;
    font-size:16px;
    color:#666;
}

.settings-wrapper {
    position: relative;
    display: inline-block;
}

/* dropdown stays attached to wrapper */
#deathSettingsMenu {
    position: absolute;
    top: 100%;      /* directly under button */
    right: 0;       /* align to button right side */
    display: none;
    z-index: 9999;
}
.records-table-wrapper{
    position: relative;
}

/* sticky footer inside table container */
.table-footer {
    position: sticky;
    bottom: 0;
    left: 0;
    right: 0;

    width: 100%;
    flex-shrink: 0;
    margin-top: auto;

    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 10px 15px;

    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);

    border-top: 1px solid #d6dbe6;

    box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.08);

    z-index: 50;
}

/* buttons */
.footer-btn{
    padding: 6px 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;

    background: #1e88e5;
    color: white;
    font-size: 12px;
}

.footer-btn:hover{
    background: #1565c0;}

.records-table-wrapper{
    display:flex;
    flex-direction:column;
    height:100%;
}

.table-container{
    display: flex;
    flex-direction: column;
    height: 325px;          /* Change to your desired height */
    min-height: 325px;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
}

.excel-wrapper{
    flex: 1;
    overflow: auto;
}

.excel-table-section{
    min-height: 100%;
}


.excel-wrapper{
    display: flex;
    flex-direction: column;
    flex: 1;

    /* 👇 This is what allows you to control height externally */
    height: var(--excel-height, 200px);

    min-height: 0;
    overflow: hidden;
}

/* ✅ THIS is now the controllable area */
.excel-table-section{
     position: relative;   /* IMPORTANT */
    overflow: auto;
    min-height: 0;

    /* remove flex:1 so height works properly */
    flex: unset;

    /* 👇 default height (you can override this) */
    height: 280px;
}

.records-table-wrapper{
    transition: all .25s ease;
}

/* Hide select column normally */
#birthWrapper .select-col{
    display:none;
}

/* Show when delete mode is active */
#tableContainer.delete-mode .select-col,
#tableContainer2.delete-mode .select-col,
#tableContainer3.delete-mode .select-col,
#tableContainer4.delete-mode .select-col,
#tableContainer5.delete-mode .select-col{
    display:table-cell;
}
/* Hide select column by default */
#tableContainer .select-col,
#tableContainer2 .select-col,
#tableContainer3 .select-col,
#tableContainer4 .select-col,
#tableContainer5 .select-col{
    display: none;
}

/* Move only the table down when delete mode is active */
#tableContainer.delete-mode .records-table,
#tableContainer2.delete-mode .records-table,
#tableContainer3.delete-mode .records-table,
#tableContainer4.delete-mode .records-table,
#tableContainer5.delete-mode .records-table{
    margin-top: 40px;
}


/* Table container */
#tableContainer,
#tableContainer2,
#tableContainer3,
#tableContainer4,
#tableContainer5{
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
    position: relative;
}
.records-table-wrapper{

    flex:1;

    overflow:auto;

    min-height:0;
}
.records-wrapper {
    height: 70vh;              /* or any fixed height */
    display: flex;
    flex-direction: column;
    overflow: hidden;          /* IMPORTANT: prevents pushing footer down */
}

/* wrapper around table */
.records-table-wrapper {
    flex: 1;                   /* takes available space */
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* scroll only the table area */
.table-container {
    flex: 1;
    overflow-y: auto;
    overflow-x: auto;
}

/* OVERLAY - push down so it doesn't hit main header */
.win-tab-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.65);
    display:flex;
    justify-content:center;
    align-items:flex-start;   /* key change */
    padding-top:90px;         /* pushes it below app header */
    z-index:9999;
}

/* WINDOW (DARK + 3D) */
.win-tab-window{
    width:600px;
    background:linear-gradient(145deg,#141414,#0f0f10);
    border:1px solid #2c2c2c;
    border-radius:0;
    box-shadow:
        0 20px 60px rgba(0,0,0,0.8),
        inset 0 1px 0 rgba(255,255,255,0.05),
        inset 0 -2px 5px rgba(0,0,0,0.9);
    font-family:Segoe UI, Arial, sans-serif;
    color:#e5e5e5;
}

/* HEADER */
.win-tab-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:10px;
    background:linear-gradient(180deg,#1f1f22,#121214);
    border-bottom:1px solid #2b2b2b;
}

/* TITLE */
.win-tab-title{
    font-size:13px;
    font-weight:600;
    color:#f2f2f2;
}

/* CLOSE BUTTON */
.win-tab-close{
    width:26px;
    height:26px;
    border:none;
    background:#2a2a2a;
    color:#fff;
    cursor:pointer;
    box-shadow:inset 0 2px 5px rgba(0,0,0,0.7);
}

.win-tab-close:hover{
    background:#3a3a3a;
}

/* BODY */
.win-tab-body{
    padding:10px;
}

/* TOP ROW */
.win-tab-top{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:8px;
    margin-bottom:10px;
}

/* LABELS */
.win-field label{
    font-size:11px;
    color:#aaa;
}

/* INPUTS */
.win-field input{
    width:100%;
    padding:6px;
    border:1px solid #333;
    border-radius:0;
    font-size:12px;
    background:#111;
    color:#fff;
    box-shadow:inset 0 2px 5px rgba(0,0,0,0.8);
}

/* MAIN GRID */
.win-tab-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:8px;
    margin-bottom:10px;
}

/* TILES (3D DARK BOXES) */
.tile{
    border:1px solid #2b2b2b;
    padding:6px;
    text-align:center;
    background:linear-gradient(145deg,#1a1a1d,#0f0f10);
    box-shadow:
        inset 0 2px 6px rgba(0,0,0,0.8),
        0 6px 15px rgba(0,0,0,0.6);
}

/* TILE LABEL */
.tile span{
    font-size:10px;
    display:block;
    margin-bottom:4px;
    color:#bbb;
}

.win-field input,
.tile input,
.win-tab-summary input{
    width:100%;
    padding:6px 8px;
    border:1px solid #bdbdbd;
    border-radius:0;

    background:#ffffff;
    color:#111111;

    font-size:12px;

    /* 3D inner shadow effect */
    box-shadow:
        inset 0 2px 3px rgba(0,0,0,0.12),
        inset 0 -1px 2px rgba(255,255,255,0.8);

    outline:none;
}

/* focus effect (important for "live app feel") */
.win-field input:focus,
.tile input:focus,
.win-tab-summary input:focus{
    border-color:#3b82f6;
    box-shadow:
        inset 0 2px 4px rgba(0,0,0,0.15),
        0 0 0 2px rgba(59,130,246,0.25);
}
/* SUMMARY */
.win-tab-summary{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:8px;
    margin-bottom:10px;
}

.win-tab-summary label{
    font-size:11px;
    color:#aaa;
}

.win-tab-summary input{
    width:100%;
    padding:5px;
    border:1px solid #333;
    border-radius:0;
    text-align:center;
    background:#111;
    color:#fff;
}

/* ACTIONS */
.win-tab-actions{
    display:flex;
    justify-content:flex-end;
    gap:6px;
    border-top:1px solid #2b2b2b;
    padding-top:8px;
}

.win-tab-actions button{
    padding:6px 10px;
    border:1px solid #333;
    background:#1f1f22;
    color:#fff;
    cursor:pointer;
    font-size:12px;
    box-shadow:inset 0 1px 0 rgba(255,255,255,0.05);
}

.win-tab-actions button:hover{
    background:#2a2a2a;
}

/* PRIMARY BUTTON */
.win-tab-actions .primary{
    background:#2563eb;
    border:1px solid #1d4ed8;
}

/* Chrome, Edge, Safari */
input[type=number]::-webkit-outer-spin-button,
input[type=number]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Firefox */
input[type=number] {
    -moz-appearance: textfield;
}
.tile select{
    width:100%;
    padding:5px 28px 5px 8px; /* space for arrow */
    border:1px solid #333;
    border-radius:0;
    font-size:12px;

    background-color:#0f0f10;
    color:#ffffff;

    appearance:none;
    -webkit-appearance:none;
    -moz-appearance:none;

    box-shadow:inset 0 2px 5px rgba(0,0,0,0.8);

    cursor:pointer;
}

.tile{
    position:relative;
}

/* arrow */
.tile select{
    background-image:
        linear-gradient(45deg, transparent 50%, #aaa 50%),
        linear-gradient(135deg, #aaa 50%, transparent 50%);
    background-position:
        calc(100% - 14px) calc(50% - 3px),
        calc(100% - 9px) calc(50% - 3px);
    background-size:5px 5px;
    background-repeat:no-repeat;
}

/* =========================================================
   WINDOWS 11 PROFILE POPUP
========================================================= */

#animalProfilePopup{
    display:none;
    position:fixed;
    inset:0;
    z-index:99999;

    justify-content:center;

    /* Instead of perfectly centered */
    align-items:flex-start;

    padding-top:90px;

    background:rgba(0,0,0,.55);
    backdrop-filter:blur(8px);
}

#animalProfilePopup.show{
    display:flex;
}

/* =========================================================
   WINDOW
========================================================= */
.profile-window{

    width:620px;
    max-width:92%;

    border-radius:14px;

    overflow:hidden;

    background:linear-gradient(
        180deg,
        #323846,
        #232933
    );

    border:1px solid rgba(255,255,255,.08);

    box-shadow:
        0 18px 40px rgba(0,0,0,.40),
        inset 0 1px 0 rgba(255,255,255,.08);
}

@keyframes profileOpen{

    from{

        opacity:0;
        transform:scale(.92);

    }

    to{

        opacity:1;
        transform:scale(1);

    }

}

/* =========================================================
   TITLE BAR
========================================================= */

.profile-titlebar{

    display:flex;
    justify-content:space-between;
    align-items:center;

    padding:10px 14px;

    background:linear-gradient(
        180deg,
        #404857,
        #313844
    );

    border-bottom:1px solid rgba(255,255,255,.08);
}

.profile-title{

    display:flex;

    align-items:center;

    gap:10px;

    color:#ffffff;

    font-size:14px;

    font-weight:600;
}



.profile-close{

    width:30px;

    height:30px;

    border:none;

    border-radius:10px;

    cursor:pointer;

    color:#ffffff;

 font-size:13px;

    background:linear-gradient(
        180deg,
        #565f70,
        #414958
    );

    transition:.2s;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.12),
        0 3px 8px rgba(0,0,0,.25);
}

.profile-close:hover{

    background:#e81123;

    transform:translateY(-1px);
}

/* =========================================================
   BODY
========================================================= */

.profile-body{

    padding:12px;
}

/* =========================================================
   TABLE
========================================================= */

.profile-table{

    width:100%;

    border-collapse:collapse;

    background:#2b313c;

    border-radius:12px;

    overflow:hidden;

    color:#ffffff;

    font-size:11px;

    border:1px solid #586273;
}

.profile-table tr{

    transition:.18s;
}

.profile-table tr:hover{

    background:#394352;
}

.profile-table td{

    padding:8px 10px;

    border:1px solid #586273;
}

.profile-table td:first-child{

    width:170px;

    background:#353d4b;

    font-weight:200;

    color:#f3f3f3;
}

.profile-table td:last-child{

    color:#ffffff;
}

.profile-table i{

    width:16px;

    color:#7ec8ff;

    margin-right:6px;

    text-align:center;

 font-size:11px;
}

/* =========================================================
   FOOTER
========================================================= */

.profile-footer{

    display:flex;
    justify-content:flex-end;
    align-items:center;

    gap:10px;

    padding:10px 14px;   /* fixed padding (top/bottom + left/right) */

    min-height:52px;     /* gives the footer proper height */

    background:#2d333e;

    border-top:1px solid rgba(255,255,255,.08);
}

/* =========================================================
   BUTTONS
========================================================= */
.profile-btn{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:4px;

    padding:4px 8px;   /* smaller padding */

    min-width:unset;   /* remove forced width */
    height:auto;       /* allow natural height */

    border:none;
    border-radius:6px;

    cursor:pointer;
    font-size:10px;    /* smaller text */
    font-weight:600;

    color:#ffffff;

    transition:.18s;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.12),
        0 3px 8px rgba(0,0,0,.30);
}
.profile-btn:hover{

    transform:translateY(-1px);

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.15),
        0 6px 12px rgba(0,0,0,.35);
}

.profile-btn:active{

    transform:translateY(1px);
}

.profile-btn i{

    font-size:11px;
}


/* =========================================================
   BUTTON COLOURS
========================================================= */

.profile-btn.primary{

    background:linear-gradient(
        180deg,
        #3498ff,
        #1d74e8
    );
}

.profile-btn.secondary{

    background:linear-gradient(
        180deg,
        #5c6576,
        #434c59
    );
}

.profile-btn.danger{

    background:linear-gradient(
        180deg,
        #ff6666,
        #d93d3d
    );
}
/* =====================================================
   POPUP OVERLAY ===================================================== */

.popup-overlay{
    position:fixed;
    top:20px;
    right:20px;

    width:340px;
    height:auto;   /* 👈 this is the correct "match content height" */

    display:none;

    justify-content:flex-start;
    align-items:flex-start;

    z-index:999999;
}

/* =====================================================
   POPUP BOX ===================================================== */

.popup-box{
    width:100%;
    background:#ffffff;
    border:1px solid #d9d9d9;
    border-radius:6px;
    box-shadow:0 8px 20px rgba(0,0,0,.15);
    overflow:hidden;
    font-family:Arial, Helvetica, sans-serif;
    transform:translateX(400px);
    transition:all .35s ease;
}

/* show animation */

.popup-overlay[style*="flex"] .popup-box{
    transform:translateX(0);
}

/* =====================================================
   POPUP HEADER ===================================================== */

.popup-header{
    padding:10px 14px;
    font-size:14px;
    font-weight:600;
    color:#222;
    background:#f8f9fa;
    border-bottom:1px solid #e5e7eb;
}

/* =====================================================
   POPUP BODY ===================================================== */

.popup-body{
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px;
    font-size:13px;
    color:#555;
    line-height:18px;
}

/* =====================================================
   POPUP FOOTER ===================================================== */

.popup-footer{
    padding:10px 14px;
    text-align:right;
    border-top:1px solid #e5e7eb;
    background:#fafafa;
}

/* =====================================================
   BUTTON ===================================================== */

.popup-btn{
    border:none;
    background:#2563eb;
    color:#fff;
    padding:7px 16px;
    border-radius:4px;
    cursor:pointer;
    font-size:12px;
    font-weight:600;
}

.popup-btn:hover{
    opacity:.9;
}

/* =====================================================
   ICONS ===================================================== */

.popup-icon{
    width:36px;
    height:36px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
    font-size:18px;
    font-weight:bold;
    color:#fff;
}

/* =====================================================
   SUCCESS ===================================================== */

.popup-success{
    border-left:5px solid #28a745;
}

.success-icon{
    background:#28a745;
}

/* =====================================================
   WARNING ===================================================== */

.popup-warning{
    border-left:5px solid #ff9800;
}

.warning-icon{
    background:#ff9800;
}

/* =====================================================
   ERROR ===================================================== */

.popup-error{
    border-left:5px solid #dc3545;
}

.error-icon{
    background:#dc3545;
}

/* =====================================================
   LOADING ===================================================== */

.popup-loading{
    border-left:5px solid #2563eb;
}

/* =====================================================
   LOADER ===================================================== */

.loader{
    width:28px;
    height:28px;
    border:3px solid #e5e7eb;
    border-top:3px solid #2563eb;
    border-radius:50%;
    animation:spin .8s linear infinite;
    flex-shrink:0;
}

@keyframes spin{
    0%{
        transform:rotate(0deg);
    }

    100%{
        transform:rotate(360deg);
    }
}

.popup-overlay{
    animation:popupFade .25s ease;
}

@keyframes popupFade{
    from{
        opacity:0;
    }

    to{
        opacity:1;
    }
}
    </style>
</head>

<body>

<!-- ================= NAVBAR ================= -->

<div class="navbar">

    <!-- LEFT -->
    <div class="logo-section">

        <img src="icon.png" alt="Logo">

        <img src="head.png" alt="Header Logo">

    </div>

    <!-- RIGHT -->
<div class="top-icons">

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
        <!-- Profile -->
<div class="profile-dropdown">

    <div class="profile" onclick="toggleProfileMenu()">

        <div class="profile-icon">
           <i class="fa-solid fa-user"></i>
        </div>

        <div>
            <?php echo htmlspecialchars($_SESSION["username"]); ?>
        </div>
    </div>

    <div class="profile-menu" id="profileMenu">
<?php 
if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['super_admin', 'hooper_admin'])) { 
?>

<a href="admin_dashboard.php" class="admin-item">
    <i class="fa-solid fa-user-shield"></i>
    Admin Dashboard
</a>

<?php } ?>

 <a href="create_report.php" class="create-report-item">
        <i class="fa-solid fa-file-circle-plus"></i>
        Create Report
    </a>

        <a href="#">
            <i class="fa-solid fa-user"></i>
            My Profile
        </a>

        <a href="#">
            <i class="fa-solid fa-gear"></i>
            Settings
        </a>

        <a href="#">
            <i class="fa-solid fa-lock"></i>
            Change Password
        </a>

        <a href="logout.php" class="logout-item">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </a>

    </div>

</div>

        <!-- Logout -->
        <a href="logout.php" class="logout-btn">
            Logout
        </a>

    </div>

</div>

<!-------------------------Hide----------------------------------!>
<div id="hideConfirmPopup" class="inner-popup" style="display:none;">

    <div class="inner-popup-box">

        <div class="popup-header">
            Confirm Action
        </div>

        <div class="popup-body">

            <div class="popup-icon warning-icon">
                ⚠
            </div>

            Are you sure you want to Delete the selected records?

        </div>


        <div style="display:flex;gap:10px;justify-content:center;padding:15px;">

            <button
                class="popup-btn"
                onclick="confirmHideSelected()">

                Delete

            </button>

            <button
                class="popup-btn"
                onclick="closeHideConfirmPopup()">

                Cancel

            </button>

        </div>

    </div>

</div>
<!---------------------------------Success-------------------->
<div id="BirthHideConfirmPopup" class="inner-popup" style="display:none;">

    <div class="inner-popup-box">

        <div class="popup-header">
            Confirm Action
        </div>

        <div class="popup-body">

            <div class="popup-icon warning-icon">
                ⚠
            </div>

            Are you sure you want to Delete the selected records?

        </div>
        <div style="display:flex;gap:10px;justify-content:center;padding:15px;">

            <button
                class="popup-btn"
                onclick="confirmHideSelected()">

                Delete

            </button>

            <button
                class="popup-btn"
                onclick="closeHideConfirmPopup()">

                Cancel

            </button>

        </div>

    </div>

</div>

<div id="hideSuccessPopup" class="inner-popup" style="display:none;">

    <div class="inner-popup-box">

        <div class="popup-header">
            Success
        </div>

        <div class="popup-body">

            <div class="popup-icon success-icon">
                ✔
            </div>

            Selected records were successfully deleted.

        </div>

        <button
            class="popup-btn"
            onclick="closeHideSuccessPopup()">

            OK

        </button>

    </div>

</div>

<!-- ================= NOT FOUND POPUP ================= -->

<div class="popup-overlay" id="notFoundPopup">

    <div class="popup-box popup-error">

        <div class="popup-header">
            Animal Not Found
        </div>

        <div class="popup-body">

            <div class="popup-icon error-icon">
                ✖
            </div>

            Animal does not exist in Master Register.

        </div>

        <div class="popup-footer">

            <button class="popup-btn"
                    onclick="closeNotFoundPopup()">

                CLOSE

            </button>

        </div>

    </div>

</div>
<!-- ================= MAIN ================= -->
<div class="popup-overlay" id="warningPopup">

    <div class="popup-box popup-warning">

        <div class="popup-header">
            Warning
        </div>

        <div class="popup-body">

            <div class="popup-icon warning-icon">
                !
            </div>

            <span id="warningPopupMessage">
                Warning message.
            </span>

        </div>

        <div class="popup-footer">

            <button class="popup-btn"
                    onclick="closeWarningPopup()">

                OK

            </button>

        </div>

    </div>

</div>

<div class="popup-overlay" id="successPopup">

    <div class="popup-box popup-success">

        <div class="popup-header">
            Success
        </div>

        <div class="popup-body">

            <div class="popup-icon success-icon">
                ✔
            </div>

            <span id="successPopupMessage">
                Operation completed successfully.
            </span>

        </div>

        <div class="popup-footer">

            <button class="popup-btn"
                    onclick="closeSuccessPopup()">

                OK

            </button>

        </div>

    </div>

</div>

<div class="popup-overlay" id="loadingPopup">

    <div class="popup-box popup-loading">

        <div class="popup-header">
            Processing Request
        </div>

        <div class="popup-body">

            <div class="loader"></div>

            Saving data to database...
            Please wait.

        </div>

    </div>

</div>

<div class="popup-overlay" id="errorPopup">

    <div class="popup-box popup-error">

        <div class="popup-header">
            Database Error
        </div>

        <div class="popup-body">

            <div class="popup-icon error-icon">
                ✖
            </div>

            <span id="errorPopupMessage">
                Database operation failed.
            </span>

        </div>

        <div class="popup-footer">

            <button class="popup-btn"
                    onclick="closeErrorPopup()">

                OK

            </button>

        </div>

    </div>

</div>

<div class="container">

    <!-- SIDEBAR -->

    <div class="sidebar">

        <div class="menu-title">
            MAIN MENU
        </div>

        <a href="home.php">
            <i class="fa-solid fa-house"></i>
            <span>Dashboard</span>
        </a>

        <a href="large_register.php">
            <i class="fa-solid fa-cow"></i>
            <span>Large Stock</span>
        </a>

        <a href="small_register.php">
            <i class="fa-solid fa-paw"></i>
            <span>Small Stock</span>
        </a>

        <a href="reports.php">
            <i class="fa-solid fa-chart-column"></i>
            <span>Reports</span>
        </a>

        <a href="kraal_owners.php">
            <i class="fa-solid fa-warehouse"></i>
            <span>Diptanks & Kraals</span>
        </a>

        <a href="about.php">
            <i class="fa-solid fa-circle-info"></i>
            <span>About</span>
        </a>

    </div>

    <!-- CONTENT -->

<div class="content">

    <div class="main-card">

        <!-- CONTENT AREA -->

        <!-- TOP TABS -->

        <div class="tab-header">

            <button class="tab-btn active" data-tab="cattle">
                Cattle
            </button>

            <button class="tab-btn" data-tab="births">
                Births
            </button>

            <button class="tab-btn" data-tab="permitsin">
                Permits In
            </button>

            <button class="tab-btn" data-tab="permitsout">
                Permits Out
            </button>

            <button class="tab-btn" data-tab="deaths">
                Deaths
            </button>

            <button class="tab-btn" data-tab="master">
                Master
            </button>

            <button class="tab-btn" data-tab="census">
                Stock Census
            </button>

        </div>


        <!-- CONTENT AREA -->

        <!-- ================= CATTLE TAB ================= -->

        <div class="tab-content active" id="cattle">

                <!-- ================= LEFT SIDE ================= -->

                <div class="left-content">

                    <!-- SEARCH TABLE -->
<div class="records-wrapper" id="dailyRecordsWrapper">

                <div class="records-topbar">
                    <strong style="color:#1e293b;font-size:12px;">
                        Daily Cattle Records
                    </strong>
                </div>

                        <!-- SEARCH BAR -->

                        <div class="records-topbar">

                            <form action="" id="searchDailyRecordsForm" class="records-search">
 <input
            type="text"
            name="diptank_no"
            placeholder="Diptank Number"
            required>

        <input
            type="date"
            name="date"
            required>

        <button
            type="button"
            class="records-btn"
            onclick="searchDailyRecords()">

            Search

        </button>


				<button type="button" class="zoom-btn" onclick="toggleTableView()">
    					🔍
				</button>
<button type="button" class="bin-btn" onclick="toggleDeleteMode()">
    🗑️
</button>
<div class="settings-wrapper">

    <button id="stng-cattle"
        type="button"
        class="bin-btn"
        onclick="toggleDeathSettingsMenu(event)">

        ⚙️

    </button>

    <div id="cattleSettingsMenu" class="tag-menu">

        <button type="button" class="tag-item"
            onclick="openDeathFilterMenu();">

            <span>Filter Records</span>
            <span class="menu-arrow">&gt;</span>

        </button>

    </div>

</div>
 <button
        type="button"
        class="create-record-btn"
        onclick="openRecordPopup()"
 	style="margin-left:auto;">

        <i class="fa-solid fa-file-circle-plus"></i>

        <span>Create Record</span>

    </button>

                            </form>

                        </div>


                      <div class="records-table-wrapper">

    <div id="tableContainer" class="table-container">


        <div class="expanded-header">
            <h3>Cattle Register</h3>

            <button type="button"
                    class="close-expanded"
                    onclick="closeTableView()">
                ✕
            </button>
        </div>

        <div class="excel-wrapper">
<div id="deleteBar" class="delete-bar">
    
    <label class="mark-all">
        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
        Mark All
    </label>

    <div class="actions">
        <button onclick="openHideConfirmPopup()" class="btn-3d danger">
            🗑 Delete
        </button>

        <button onclick="toggleDeleteMode()" class="btn-3d neutral">
            Cancel
        </button>
    </div>

</div>

            <div class="excel-table-section">

                <table class="records-table">

                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>D/T</th>
                            <th>KRL</th>
                            <th>CB</th>
                            <th>PI</th>
                            <th>TF</th>
                            <th>PO</th>
                            <th>TT</th>
                            <th>DTH</th>
                            <th>ABS</th>
                            <th>AS</th>
                            <th>AL</th>
                            <th>CLV</th>
                            <th>MIS</th>
                            <th>EXT</th>
                            <th>OR</th>
                            <th>DIP</th>
                            <th class="select-col">Select</th>
                        </tr>
                    </thead>
<?php

include_once 'config.php';

$sql = "
    SELECT *
    FROM cattle_register
    WHERE id NOT IN (
        SELECT record_id
        FROM hidden_records
    )
    ORDER BY date DESC, id DESC
";

$result = mysqli_query($link, $sql);

?>
<tbody>

<?php if(mysqli_num_rows($result) > 0): ?>

    <?php while($row = mysqli_fetch_assoc($result)): ?>

        <tr data-id="<?= $row['id']; ?>">

            <td><?= date('d/m/Y', strtotime($row['date'])); ?></td>

            <td><?= $row['diptank_no']; ?></td>

            <td><?= $row['kraal_no']; ?></td>

            <td><?= $row['calves_born']; ?></td>

            <td><?= $row['permits_in']; ?></td>

            <td><?= $row['transfers_from']; ?></td>

            <td><?= $row['permits_out']; ?></td>

            <td><?= $row['transfers_to']; ?></td>

            <td><?= $row['deaths']; ?></td>

            <td><?= $row['absent']; ?></td>

            <td><?= $row['ab_sick']; ?></td>

            <td><?= $row['ab_limbing']; ?></td>

            <td><?= $row['calving']; ?></td>

            <td><?= $row['missing']; ?></td>

            <td><?= $row['extras']; ?></td>

            <td><?= $row['on_register']; ?></td>

            <td><?= $row['dipped']; ?></td>

            <td class="select-col">

                <input
                    type="checkbox"
                    class="row-check"
                    data-id="<?= $row['id']; ?>">

            </td>

        </tr>

    <?php endwhile; ?>

<?php else: ?>

    <tr>

        <td colspan="18"
            style="text-align:center;padding:20px;color:#64748b;">

            No daily records found.

        </td>

    </tr>

<?php endif; ?>

</tbody>

                </table>

            </div>

        </div>

        <!-- FOOTER MUST BE INSIDE tableContainer -->
        <div class="table-footer">

            <div class="footer-info">
                Showing latest records
            </div>

            <div class="footer-actions">

                <button class="footer-btn" onclick="refreshTable()">
                    🔄 Refresh
                </button>

                <button class="footer-btn" onclick="scrollToTop()">
                    ⬆ Top
                </button>

            </div>

        </div>

    </div>

</div>
 </div>
 </div>
 </div>

        <!-- ================= BIRTHS TAB ================= -->

 <div class="tab-content" id="births">

    <div class="main-layout">

        <div class="left-content">

            <div class="records-wrapper" id="birthWrapper">

                <div class="records-topbar">

                    <strong style="color:#1e293b;font-size:12px;">

                        Recent Birth Records

                    	</strong>

                		</div>
					<div class="records-topbar">

                            <form action="" method="post" class="records-search">

                                <input type="text"
                                       name="search"
                                       placeholder="Diptank No">

                                <input type="date"
                                       name="date">

                                <button type="submit"
                                        name="save"
                                        class="records-btn">

                                    Search

                                </button>

				<button type="button" class="zoom-btn" onclick="toggleTableViewBirths()">
    					🔍
				</button>
<button
    type="button"
    class="bin-btn"
    onclick="toggleBirthDeleteMode()">
    🗑️
</button>
<button
        type="button"
        class="create-record-btn"
        onclick="openBirthPopup()"
 	style="margin-left:auto;">

        <i class="fa-solid fa-file-circle-plus"></i>

        <span>Create Record</span>

    </button>

                            </form>

                        </div>

                <div class="records-table-wrapper">

			<div id="tableContainer2" class="table-container">


			 <div class="expanded-header">

    <h3>Births Register</h3>

    <button type="button"
            class="close-expanded"
            onclick="closeTableViewBirths()">
        ✕
    </button>

</div>
   
 <div class="excel-wrapper">

<div id="birthDeleteBar" class="delete-bar">

    <label class="mark-all">
        <input
            type="checkbox"
            id="birthSelectAll"
            onchange="toggleBirthSelectAll(this)">
        Mark All
    </label>

    <div class="actions">

        <button
            onclick="openBirthHideConfirmPopup()"
            class="btn-3d danger">
            🗑 Delete
        </button>

        <button
            onclick="toggleBirthDeleteMode()"
            class="btn-3d neutral">
            Cancel
        </button>

    </div>

</div>

            <div class="excel-table-section">

                <table class="records-table">

    <thead>
        <tr>
            <th>Date</th>
            <th>D/T</th>
            <th>Kraal</th>
            <th>Tenure</th>
            <th>Purpose</th>
            <th>Sex</th>
            <th>Color</th>
            <th>Breed</th>
            <th>Dam ID</th>
            <th class="select-col">Select</th>
        </tr>
    </thead>

    <tbody id="birthRecords">

    
        <tr>
            <td colspan="10" class="no-data">
                No Recent Birth Records
            </td>
        </tr>

    
    </tbody>

</table>

</div>    
</div>

        <!-- FOOTER MUST BE INSIDE tableContainer -->
        <div class="table-footer">

            <div class="footer-info">
                Showing latest records
            </div>

            <div class="footer-actions">

                <button class="footer-btn" onclick="refreshTable()">
                    🔄 Refresh
                </button>

                <button class="footer-btn" onclick="scrollToTop()">
                    ⬆ Top
                </button>

            </div>

</div>
 </div>
 </div>
</div>
</div>
</div>
</div>

<!-- ================= PERMITS IN TAB ================= -->
<div class="tab-content" id="permitsin">

    <div class="main-layout">

        <div class="left-content">

            <!-- ================= WRAPPER ================= -->
            <div class="records-wrapper" id="permitinWrapper">

                <!-- TITLE -->
                <div class="records-topbar">
                    <strong style="color:#1e293b;font-size:12px;">
                        Permits In Records
                    </strong>
                </div>

                <!-- SEARCH + ACTIONS -->
                <div class="records-topbar">

                    <form class="records-search" onsubmit="return false;">

                        <input type="text"
                               id="permitInSearch"
                               placeholder="Diptank No">

                        <input type="date"
                               id="permitInDate">

                        <button type="button"
                                class="records-btn"
                                onclick="loadPermitInRecords()">
                            Search
                        </button>

                        <button type="button"
                                class="zoom-btn"
                                onclick="toggleTableViewPermitIn()">
                            🔍
                        </button>

                        <button type="button"
                                class="bin-btn"
                                onclick="togglePermitInDeleteMode()">
                            🗑️
                        </button>

                        <button type="button"
                                class="create-record-btn"
                                onclick="openPermitInPopup()"
                                style="margin-left:auto;">
                            <i class="fa-solid fa-file-circle-plus"></i>
                            <span>Create Record</span>
                        </button>

                    </form>

                </div>

                <!-- ================= TABLE ================= -->
                <div class="records-table-wrapper">

                    <div id="tableContainer3" class="table-container">

                        <!-- HEADER -->
                        <div class="expanded-header">
                            <h3>Permits In Register</h3>

                            <button type="button"
                                    class="close-expanded"
                                    onclick="closeTableViewPermitIn()">
                                ✕
                            </button>
                        </div>

                        <!-- DELETE BAR -->
                        <div id="permitInDeleteBar" class="delete-bar">

                            <label class="mark-all">
                                <input type="checkbox"
                                       id="permitInSelectAll"
                                       onchange="togglePermitInSelectAll(this)">
                                Mark All
                            </label>

                            <div class="actions">

                                <button class="btn-3d danger"
                                        onclick="openPermitInHideConfirmPopup()">
                                    🗑 Delete
                                </button>

                                <button class="btn-3d neutral"
                                        onclick="togglePermitInDeleteMode()">
                                    Cancel
                                </button>

                            </div>

                        </div>

                        <!-- TABLE -->
                        <div class="excel-wrapper">

                            <div class="excel-table-section">

                                <table class="records-table">

                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Permit No</th>
                                            <th>Diptank No</th>
                                            <th>Kraal No</th>
                                            <th>Animal ID</th>
                                            <th>Sex</th>
                                            <th>Color</th>
                                            <th>Source</th>
                                            <th class="select-col">Select</th>
                                        </tr>
                                    </thead>

                                    <!-- LIVE DATA GOES HERE -->
                                    <tbody id="permitInRecords"></tbody>

                                </table>

                            </div>

                        </div>

                        <!-- FOOTER -->
                        <div class="table-footer">

                            <div class="footer-info">
                                Showing latest permit-in records
                            </div>

                            <div class="footer-actions">

                                <button type="button"
                                        class="footer-btn"
                                        onclick="loadPermitInRecords()">
                                    🔄 Refresh
                                </button>

                                <button type="button"
                                        class="footer-btn"
                                        onclick="scrollPermitInTop()">
                                    ⬆ Top
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- ================= PERMITS OUT TAB ================= -->

<div class="tab-content" id="permitsout">

    <div class="main-layout">

        <div class="left-content">  
        
	   <div class="records-wrapper" id="permitoutWrapper">

                <div class="records-topbar">
                    <strong style="color:#1e293b;font-size:12px;">
                        Permits Out Records
                    </strong>
                </div>
			<div class="records-topbar">

                            <form action="" method="post" class="records-search">

                                <input type="text"
                                       name="search"
                                       placeholder="Diptank No">

                                <input type="date"
                                       name="date">

                                <button type="submit"
                                        name="save"
                                        class="records-btn">

                                    Search

                                </button>

				<button type="button" class="zoom-btn" onclick="toggleTableViewPermitOut()">
    					🔍
				</button>
<button
    type="button"
    class="bin-btn"
    onclick="togglePermitOutDeleteMode()">

    🗑️

</button>
 <button
        type="button"
        class="create-record-btn"
        onclick="openPermitOutPopup()"
 	style="margin-left:auto;">

        <i class="fa-solid fa-file-circle-plus"></i>

        <span>Create Record</span>

    </button>

                            </form>

                        </div>
<div class="records-table-wrapper">

			<div id="tableContainer4" class="table-container">
			 <div class="expanded-header">

    <h3>Permits Out Register</h3>

    <button type="button"
            class="close-expanded"
            onclick="closeTableViewPermitOut()">
        ✕
    </button>

</div>



        <div class="excel-wrapper">
<div id="permitOutDeleteBar" class="delete-bar">

    <label class="mark-all">

        <input
            type="checkbox"
            id="permitOutSelectAll"
            onchange="togglePermitOutSelectAll(this)">

        Mark All

    </label>

    <div class="actions">

        <button
            class="btn-3d danger"
            onclick="openPermitOutHideConfirmPopup()">

            🗑 Delete

        </button>

        <button
            class="btn-3d neutral"
            onclick="togglePermitOutDeleteMode()">

            Cancel

        </button>

    </div>

</div>

            <div class="excel-table-section">

                <table class="records-table">

                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Permit No</th>
                            <th>Diptank</th>
                            <th>Kraal</th>
                            <th>Destination</th>
                            <th>Animal ID</th>
			    <th class="select-col">Select</th>
                        </tr>
                        </thead>

<tbody id="permitOutRecords">

        <tr data-id="1">



            <td>2026-06-29</td>
            <td>122234</td>
            <td>444</td>
            <td>1</td>
            <td>456 / 2</td>
            <td>432-0000</td>
            <td class="select-col">
                <input
                    type="checkbox"
                    class="permitout-row-check"
                    data-id="1">
            </td>

        </tr>


</tbody>


</table>



</div>

</div>
<!-- FOOTER -->
<div class="table-footer">

    <div class="footer-info">
        Showing latest birth records
    </div>

    <div class="footer-actions">


      
  <button
            type="button"
            class="footer-btn"
            onclick="refreshBirthTable()">

            🔄 Refresh

        </button>

        <button
            type="button"
            class="footer-btn"
            onclick="scrollBirthTableTop()">

            ⬆ Top

        </button>

    </div>
</div>
</div>
</div>

</div>
</div>
</div>
</div>

<!-- ================= DEATHS TAB ================= -->

<div class="tab-content" id="deaths">

    <div class="main-layout">

        <div class="left-content">          
<div class="records-wrapper" id="deathWrapper">

                <div class="records-topbar">
                    <strong style="color:#1e293b;font-size:12px;">
                        Deaths
                    </strong>
                </div>
<div class="records-topbar">

                            <form action="" method="post" class="records-search">

                                <input type="text"
                                       name="search"
                                       placeholder="Diptank No">

                                <input type="date"
                                       name="date">

                                <button type="submit"
                                        name="save"
                                        class="records-btn">

                                    Search

                                </button>

				<button type="button" class="zoom-btn" onclick="toggleTableViewDeath()">
    					🔍
				</button>
<button
    type="button"
    class="bin-btn"
    onclick=" toggleDeathDeleteMode()">
    🗑️
</button>
 <button
        type="button"
        class="create-record-btn"
        onclick="openDeathPopup()"
 	style="margin-left:auto;">

        <i class="fa-solid fa-file-circle-plus"></i>

        <span>Create Record</span>

    </button>

                            </form>

                        </div>

			<div id="tableContainer5" class="table-container">
			 <div class="expanded-header">

    <h3>Deaths Register</h3>

    <button type="button"
            class="close-expanded"
            onclick="closeTableViewDeath()">
        ✕
    </button>

</div>

        <div class="excel-wrapper">

<div id="deathDeleteBar" class="delete-bar">
    
    <label class="mark-all">
        <input type="checkbox" id="deathsSelectAll" onchange="toggleDeathSelectAll(this)">
        Mark All
    </label>

    <div class="actions">
        <button onclick="openDeathHideConfirmPopup()" class="btn-3d danger">
            🗑 Delete
        </button>

        <button onclick="toggleDeathDeleteMode()" class="btn-3d neutral">
            Cancel
        </button>
    </div>

</div>
            <div class="excel-table-section">

                <table class="records-table">

        <thead>
        <tr>

            <th>Date</th>
            <th>Animal ID</th>
            <th>Diptank</th>
            <th>Kraal</th>
            <th>Reason</th>
            <th>Suspicions</th>

            <th class="select-col">Select</th>

        </tr>
        </thead>

        <tbody id="deathRecords">


<tr>
    <td colspan="7" class="no-data">
        No Death Records
    </td>
</tr>


        </tbody>
</table>



</div>

</div>
<!-- FOOTER -->
<div class="table-footer">

    <div class="footer-info">
        Showing latest birth records
    </div>

    <div class="footer-actions">


      
  <button
            type="button"
            class="footer-btn"
            onclick="refreshBirthTable()">

            🔄 Refresh

        </button>

        <button
            type="button"
            class="footer-btn"
            onclick="scrollBirthTableTop()">

            ⬆ Top

        </button>

    </div>
</div>
</div>
</div>

</div>
</div>
</div>



<!-- ================= MASTER TAB ================= -->
<div class="tab-content" id="master">

    <div class="main-layout">

        <div class="left-content">
<div class="records-wrapper" id="masterWrapper">

                <!-- MASTER REGISTER TABLE -->

                <div class="records-wrapper"
                     style="flex:1; min-width:48%;">

                    <div class="records-topbar">

                        <strong style="color:#1e293b;font-size:12px;">
                            Master Register
                        </strong>

                    </div>
<div class="records-topbar">

                            <form action="" method="post" class="records-search">

                                <input type="text"
                                       name="search"
                                       placeholder="Diptank No">

                                <input type="date"
                                       name="date">

                                <button type="submit"
                                        name="save"
                                        class="records-btn">

                                    Search

                                </button>

				<button type="button" class="zoom-btn" title="Expand" onclick="toggleTableViewMaster()">
    					🔍
				</button>
<button type="button" class="bin-btn" title="Delete" onclick="toggleMasterDeleteMode()">
    🗑️
</button>
<button
    type="button"
    class="bin-btn" title="Operations"
    onclick="openFilterPopup()">

    ⚙️

</button>
<button
    type="button"
    class="bin-btn" title="Diptanks"
     onclick="openFatePopup()">

    🏠

</button>
<div class="tag-dropdown">
<button type="button"
    class="bin-btn" onclick="toggleTagMenu()" title="Eartagging">
    🏷️
</button>
<div id="tagMenu" class="tag-menu">

        <button type="button" class="tag-item"
        onclick="loadEartagRequests();">
    Request Eartags
</button>
        <button type="button"
        class="tag-item"
        onclick="loadApplyEartags();">
    Apply Eartags

</button>
        <button class="tag-item">Return Eartags</button>

    </div>
</div>
                            </form>

                        </div>
<div class="records-table-wrapper">

			<div id="tableContainer6" class="table-container">
			 <div class="expanded-header">

    <h3>Mater Register</h3>

    <button type="button"
            class="close-expanded"
            onclick="closeTableViewMaster()">
        ✕
    </button>

</div>

                    <div class="records-table-wrapper"
                         style="max-height:500px;overflow:auto;">

                        <table class="records-table">

                            <thead>

                            <tr>

                                <th>Animal ID</th>
                                <th>Birth</th>
                                <th>Sex</th>
                                <th>Breed</th>
                                <th>Kraal</th>
                                <th>D/T</th>

                            </tr>

                            </thead>

                           <tbody id="masterRecords">


<tr onclick="openAnimalPopup(
'54',
'3456',
'2026-07-05',
'Male',
'Nguni',
'Red',
'1',
'4441',
'2026-07-05',
'23456',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>3456</td>
    <td>2026-07-05</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>4441</td>

</tr>


<tr onclick="openAnimalPopup(
'53',
'432',
'2026-07-05',
'Male',
'Nguni',
'Red',
'21',
'444',
'2026-07-05',
'23',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>432</td>
    <td>2026-07-05</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>21</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'52',
'34567890876',
'2026-07-05',
'Male',
'Nguni',
'Red',
'21',
'444',
'2026-07-05',
'34567876',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>34567890876</td>
    <td>2026-07-05</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>21</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'51',
'77777',
'2026-07-05',
'Male',
'Nguni',
'Red',
'1',
'444',
'2026-07-05',
'900087',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>77777</td>
    <td>2026-07-05</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'50',
'77777',
'2026-07-05',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'12345',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>77777</td>
    <td>2026-07-05</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'49',
'000',
'2026-07-05',
'Male',
'Nguni',
'Red',
'1',
'444',
'2026-07-05',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>000</td>
    <td>2026-07-05</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'48',
'000',
'2026-07-05',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>000</td>
    <td>2026-07-05</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'47',
'445-2123',
'2026-07-05',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'23',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-2123</td>
    <td>2026-07-05</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'46',
'432',
'2026-07-04',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>432</td>
    <td>2026-07-04</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'45',
'PH444-8853',
'2026-07-04',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>PH444-8853</td>
    <td>2026-07-04</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'44',
'PH444-8851',
'2026-07-04',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>PH444-8851</td>
    <td>2026-07-04</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'43',
'445-2280',
'2026-07-04',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-2280</td>
    <td>2026-07-04</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'42',
'445-2279',
'2026-07-04',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-2279</td>
    <td>2026-07-04</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'41',
'445-2270',
'2026-07-04',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-2270</td>
    <td>2026-07-04</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'40',
'438-1771',
'2026-07-04',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'422',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>438-1771</td>
    <td>2026-07-04</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'39',
'445-275',
'2026-07-03',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-275</td>
    <td>2026-07-03</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'38',
'445-274',
'2026-07-03',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-274</td>
    <td>2026-07-03</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'37',
'445-273',
'2026-07-03',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-273</td>
    <td>2026-07-03</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'36',
'445-272',
'2026-07-03',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-272</td>
    <td>2026-07-03</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'35',
'445-271',
'2026-07-03',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-271</td>
    <td>2026-07-03</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'34',
'445-270',
'2026-07-03',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-270</td>
    <td>2026-07-03</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'33',
'445-254',
'2026-07-03',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-254</td>
    <td>2026-07-03</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'32',
'445-253',
'2026-07-03',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-253</td>
    <td>2026-07-03</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'31',
'445-252',
'2026-07-03',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-252</td>
    <td>2026-07-03</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'30',
'445-251',
'2026-07-03',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-251</td>
    <td>2026-07-03</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'29',
'445-250',
'2026-07-03',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'000',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-250</td>
    <td>2026-07-03</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'28',
'445-8851',
'2026-07-02',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'444',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-8851</td>
    <td>2026-07-02</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'27',
'09876',
'2026-07-02',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'98789',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>09876</td>
    <td>2026-07-02</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'26',
'445-2240',
'2026-07-02',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'Dehorned',
''
)"
style="cursor:pointer;">

    <td>445-2240</td>
    <td>2026-07-02</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'25',
'445-238',
'2026-07-02',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-238</td>
    <td>2026-07-02</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'24',
'445-237',
'2026-07-02',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-237</td>
    <td>2026-07-02</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'23',
'445-236',
'2026-07-02',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-236</td>
    <td>2026-07-02</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'22',
'445-235',
'2026-07-02',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-235</td>
    <td>2026-07-02</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'21',
'445-234',
'2026-07-02',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-234</td>
    <td>2026-07-02</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'20',
'445-233',
'2026-07-02',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-233</td>
    <td>2026-07-02</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'19',
'445-232',
'2026-07-02',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-232</td>
    <td>2026-07-02</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'18',
'445-231',
'2026-07-02',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-231</td>
    <td>2026-07-02</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'17',
'445-230',
'2026-07-02',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-230</td>
    <td>2026-07-02</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'16',
'PH444-0994',
'2026-06-29',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'445-227',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>PH444-0994</td>
    <td>2026-06-29</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'15',
'PH444-9016',
'2026-06-29',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'445-227',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>PH444-9016</td>
    <td>2026-06-29</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'14',
'PH444-7122',
'2026-06-29',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'445-227',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>PH444-7122</td>
    <td>2026-06-29</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'13',
'PH444-4394',
'2026-06-29',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'445-227',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>PH444-4394</td>
    <td>2026-06-29</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'12',
'PH444-6730',
'2026-06-29',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'445-228',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>PH444-6730</td>
    <td>2026-06-29</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'11',
'PH444-8404',
'2026-06-29',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'445-229',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>PH444-8404</td>
    <td>2026-06-29</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'10',
'445-229',
'2026-06-29',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-229</td>
    <td>2026-06-29</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'9',
'445-228',
'2026-06-29',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-228</td>
    <td>2026-06-29</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'8',
'445-227',
'2026-06-29',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-227</td>
    <td>2026-06-29</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'7',
'445-226',
'2026-06-29',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-226</td>
    <td>2026-06-29</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'6',
'445-225',
'2026-06-29',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-225</td>
    <td>2026-06-29</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'5',
'445-224',
'2026-06-29',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-224</td>
    <td>2026-06-29</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


<tr onclick="openAnimalPopup(
'4',
'445-223',
'2026-06-29',
'Male',
'Nguni',
'Red',
'1',
'444',
'',
'24',
'None',
'SNL'
)"
style="cursor:pointer;">

    <td>445-223</td>
    <td>2026-06-29</td>
    <td>Male</td>
    <td>Nguni</td>
    <td>1</td>
    <td>444</td>

</tr>


                            </tbody>

  </table>

<!-- FOOTER -->
<div class="table-footer">

    <div class="footer-info">
        Showing latest birth records
    </div>

    <div class="footer-actions">

        <button
            type="button"
            class="footer-btn"
            onclick="refreshBirthTable()">

            🔄 Refresh

        </button>

        <button
            type="button"
            class="footer-btn"
            onclick="scrollBirthTableTop()">

            ⬆ Top

        </button>

    </div>

</div>

                    </div>

                </div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- WINDOWS 11 ANIMAL PROFILE POPUP -->

<div id="animalProfilePopup" class="popup-overlay">

    <div class="profile-window">

        <!-- WINDOW HEADER -->

        <div class="profile-titlebar">

            <div class="profile-title">

                <div class="profile-icon">
                    <i class="fa-solid fa-paw"></i>
                </div>

                <span>Animal Profile</span>

            </div>

            <button
                class="profile-close"
                onclick="closeAnimalPopup()">

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>

        <!-- CONTENT -->

        <div class="profile-body">

            <table class="profile-table">

                <tbody>

                    <tr>

                        <td>
                            <i class="fa-solid fa-fingerprint"></i>
                            Animal ID
                        </td>

                        <td id="profileAnimalID"></td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-calendar-days"></i>
                            Birth Date
                        </td>

                        <td id="profileBirthDate"></td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-venus-mars"></i>
                            Sex
                        </td>

                        <td id="profileSex"></td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-dna"></i>
                            Breed
                        </td>

                        <td id="profileBreed"></td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-palette"></i>
                            Color
                        </td>

                        <td id="profileColor"></td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-map-location-dot"></i>
                            Diptank
                        </td>

                        <td id="profileDiptank"></td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-warehouse"></i>
                            Kraal
                        </td>

                        <td id="profileKraal"></td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-user-shield"></i>
                            Tenure
                        </td>

                        <td id="profileTenure"></td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-route"></i>
                            Source
                        </td>

                        <td id="profileSource"></td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                            Alterations
                        </td>

                        <td id="profileAlterations"></td>

                    </tr>

                </tbody>

            </table>

        </div>

        <!-- FOOTER -->

        <div class="profile-footer">

            <button
                id="medicalHistoryBtn"
                class="profile-btn primary">

                <i class="fa-solid fa-notes-medical"></i>

                <span>Medical History</span>

            </button>

            <button
                id="transferAnimalBtn"
                class="profile-btn secondary"
                onclick="openTransferPopup()">

                <i class="fa-solid fa-right-left"></i>

                <span>Transfer</span>

            </button>

            <button
                id="editAnimalBtn"
                class="profile-btn secondary">

                <i class="fa-solid fa-pen-to-square"></i>

                <span>Edit</span>

            </button>

            <button
                class="profile-btn danger"
                onclick="closeAnimalPopup()">

                <i class="fa-solid fa-xmark"></i>

                <span>Close</span>

            </button>

        </div>

    </div>

</div>
<div id="editAnimalPopup" class="popup-overlay">

    <div class="profile-window">

        <!-- TITLE BAR -->

        <div class="profile-titlebar">

            <div class="profile-title">

                <div class="profile-icon">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>

                <span>Edit Animal</span>

            </div>

            <button
                type="button"
                class="profile-close"
                onclick="closeEditAnimalPopup()">

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>

        <form id="editAnimalForm">

            <input
                type="hidden"
                id="editID"
                name="id">

            <div class="profile-body">

                <table class="profile-table">

                    <tr>

                        <td>
                            <i class="fa-solid fa-fingerprint"></i>
                            Animal ID
                        </td>

                        <td>
                            <input
                                type="text"
                                id="editAnimalID"
                                name="animal_id">
                        </td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-calendar-days"></i>
                            Birth Date
                        </td>

                        <td>

                            <input
                                type="date"
                                id="editBirthDate"
                                name="birth_date">

                        </td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-venus-mars"></i>
                            Sex
                        </td>

                        <td>

                            <select
                                id="editSex"
                                name="sex">

                                <option>Male</option>
                                <option>Female</option>

                            </select>

                        </td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-dna"></i>
                            Breed
                        </td>

                        <td>

                            <input
                                type="text"
                                id="editBreed"
                                name="breed">

                        </td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-palette"></i>
                            Color
                        </td>

                        <td>

                            <input
                                type="text"
                                id="editColor"
                                name="color">

                        </td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-map-location-dot"></i>
                            Diptank
                        </td>

                        <td>

                            <input
                                type="text"
                                id="editDiptank"
                                name="diptank_no">

                        </td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-warehouse"></i>
                            Kraal
                        </td>

                        <td>

                            <input
                                type="text"
                                id="editKraal"
                                name="kraal_no">

                        </td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-user-shield"></i>
                            Tenure
                        </td>

                        <td>

                            <select
                                id="editTenure"
                                name="tenure">

                                <option>SNL</option>
                                <option>TDL</option>

                            </select>

                        </td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-route"></i>
                            Source
                        </td>

                        <td>

                            <input
                                type="text"
                                id="editSource"
                                name="source">

                        </td>

                    </tr>

                    <tr>

                        <td>
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                            Alterations
                        </td>

                        <td>

                            <select
                                id="editAlterations"
                                name="alterations">

                                <option>None</option>
                                <option>Oxen</option>
                                <option>Castrated</option>
                                <option>Dehorned</option>

                            </select>

                        </td>

                    </tr>

                </table>

            </div>

            <!-- FOOTER -->

            <div class="profile-footer">

                <button
                    type="submit"
                    class="profile-btn primary">

                    <i class="fa-solid fa-floppy-disk"></i>

                    <span>Save Changes</span>

                </button>

                <button
                    type="button"
                    class="profile-btn danger"
                    onclick="closeEditAnimalPopup()">

                    <i class="fa-solid fa-xmark"></i>

                    <span>Cancel</span>

                </button>

            </div>

        </form>

    </div>

</div>

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
<div id="chatPanel" class="chat-panel">

    <div class="chat-header">

        <div class="chat-user">

            <div class="user-avatar">
                <i class="fa-solid fa-user"></i>
            </div>

            <span id="chatUserName">Conversation</span>

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


    <div class="announcements-list">


        <div class="announcement-item">

            <strong>
                Sakhile Creates Livestock Registers            </strong>

            <p>
                Veterinary Assistant has created a platform for Veterinary Officers to use in dipping and make the process of livestock registration easier.            </p>

            <small>
                2026-06-16 15:45:31            </small>

        </div>


        <div class="announcement-item">

            <strong>
                FMD is Over            </strong>

            <p>
                happuy            </p>

            <small>
                2026-06-15 21:11:02            </small>

        </div>


        <div class="announcement-item">

            <strong>
                FMD is Over            </strong>

            <p>
                It is with great news to announce the end of FMD. This is after the Veterinary Services conducted research to conclude the verdict.            </p>

            <small>
                2026-06-15 19:22:45            </small>

        </div>


        <div class="announcement-item">

            <strong>
                Mabandla Is now Vice President            </strong>

            <p>
                Please be advised that Mabandla Gwebu is the new president of the veterinary services.            </p>

            <small>
                2026-06-02 19:11:33            </small>

        </div>


</div>
</div>
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


    <div class="notification-card">

        <div class="notification-title">
            Kraal Created        </div>

        <div class="notification-message">
            Your kraal 163 was successfully registered.        </div>

        <div class="notification-time">
            <i class="fa-regular fa-clock"></i>
            05 Jul 2026 01:08        </div>

    </div>


    <div class="notification-card">

        <div class="notification-title">
            Kraal Created        </div>

        <div class="notification-message">
            Your kraal 161 was successfully registered.        </div>

        <div class="notification-time">
            <i class="fa-regular fa-clock"></i>
            04 Jul 2026 21:16        </div>

    </div>


    <div class="notification-card">

        <div class="notification-title">
            Kraal Created        </div>

        <div class="notification-message">
            Your kraal 161 was successfully registered.        </div>

        <div class="notification-time">
            <i class="fa-regular fa-clock"></i>
            04 Jul 2026 20:51        </div>

    </div>


    <div class="notification-card">

        <div class="notification-title">
            Kraal Created        </div>

        <div class="notification-message">
            Your kraal 121 was successfully registered.        </div>

        <div class="notification-time">
            <i class="fa-regular fa-clock"></i>
            04 Jul 2026 20:48        </div>

    </div>


    <div class="notification-card">

        <div class="notification-title">
            Kraal Created        </div>

        <div class="notification-message">
            Your kraal 90 was successfully registered.        </div>

        <div class="notification-time">
            <i class="fa-regular fa-clock"></i>
            04 Jul 2026 20:45        </div>

    </div>


    <div class="notification-card">

        <div class="notification-title">
            Kraal Created        </div>

        <div class="notification-message">
            Your kraal 34 was successfully registered.        </div>

        <div class="notification-time">
            <i class="fa-regular fa-clock"></i>
            04 Jul 2026 20:33        </div>

    </div>


    <div class="notification-card">

        <div class="notification-title">
            Kraal Created        </div>

        <div class="notification-message">
            Your kraal 1 was successfully registered.        </div>

        <div class="notification-time">
            <i class="fa-regular fa-clock"></i>
            04 Jul 2026 20:12        </div>

    </div>


    <div class="notification-card">

        <div class="notification-title">
            New Kraal Created        </div>

        <div class="notification-message">
            Mabandla Gwebu registered kraal 1        </div>

        <div class="notification-time">
            <i class="fa-regular fa-clock"></i>
            16 Jun 2026 20:35        </div>

    </div>


    <div class="notification-card">

        <div class="notification-title">
            New Kraal Created        </div>

        <div class="notification-message">
            Muzi Dlamibi registered kraal 0007        </div>

        <div class="notification-time">
            <i class="fa-regular fa-clock"></i>
            15 Jun 2026 21:26        </div>

    </div>


    <div class="notification-card">

        <div class="notification-title">
            New Kraal Created        </div>

        <div class="notification-message">
            Sakhile registered kraal 001        </div>

        <div class="notification-time">
            <i class="fa-regular fa-clock"></i>
            15 Jun 2026 20:01        </div>

    </div>


        </div>

    </div>

</div>
<!-- LOADING POPUP -->
<div class="popup-overlay" id="loadingPoup">

    <div class="popup-box popup-success">

        <div class="popup-body">
            ⏳ Please wait...
        </div>

    </div>

</div>

<!-- ERROR POPUP -->
<div class="popup-overlay" id="errorPoup">

    <div class="popup-box popup-error">

        <div class="popup-body">

            <div class="popup-icon error-icon">✖</div>

            Kraal does not exist.

        </div>

        <div class="popup-footer">

            <button class="popup-btn" onclick="closeError()">
                CLOSE
            </button>

        </div>

    </div>

</div>

<div id="transferPopup" class="popup-overlay">

<form method="post">

    <div class="transfer-popup">

        <div class="transfer-header">
            Transfer Animal
        </div>

        <div class="transfer-body">

            <!-- Hidden Animal ID -->
            <input type="hidden"
                   name="animal_id"
                   id="transferAnimalID">

            <div class="transfer-group">
                <label>New Kraal</label>
                <input type="number"
                       name="new_kraal_no"
                       id="newKraalNo"
                       required>
            </div>

        </div>

        <div class="transfer-footer">

            <button type="button"
                    class="transfer-btn cancel-btn"
                    onclick="document.getElementById('transferPopup').style.display='none'">
                Cancel
            </button>

            <button type="submit"
                    name="transfer"
                    id="confirmTransferBtn"
                    class="transfer-btn confirm-btn">
                Transfer
            </button>

        </div>

    </div>

</form>
</div>

<!-- CREATE RECORD POPUP -->

<div id="recordPopup" class="win-tab-overlay">

    <div class="win-tab-window">

        <!-- HEADER -->
        <div class="win-tab-header">

            <div class="win-tab-title">
                Create Dipping Record
            </div>

            <button class="win-tab-close" onclick="closeRecordPopup()">✕</button>

        </div>

        <!-- BODY -->
        <div class="win-tab-body">

            <form id="dailyRecordForm">

                <!-- TOP INFO ROW -->
                <div class="win-tab-top">

                    <div class="win-field">
                        <label>Diptank</label>
                        <input type="text" name="diptank_no"
                        value="000"
                        inputmode="numeric"
                        oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                    </div>

                    <div class="win-field">
                        <label>Kraal</label>
                        <input type="text" name="kraal_no"
                        value="000"
                        inputmode="numeric"
                        oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                    </div>

                    <div class="win-field">
                        <label>Date</label>
                        <input type="date" name="date">
                    </div>

                </div>

                <!-- MAIN GRID (ALL FIELDS HERE) -->
                <div class="win-tab-grid">

                    <div class="tile">
                        <span>Calves Born</span>
                        <input type="number" name="calves_born" readonly>
                    </div>

                    <div class="tile">
                        <span>Permits In</span>
                        <input type="number" name="permits_in" readonly>
                    </div>

                    <div class="tile">
                        <span>Transfers From</span>
                        <input type="number" name="transfers_from" readonly>
                    </div>

                    <div class="tile">
                        <span>Permits Out</span>
                        <input type="number" name="permits_out" readonly>
                    </div>

                    <div class="tile">
                        <span>Transfers To</span>
                        <input type="number" name="transfers_to" readonly>
                    </div>

                    <div class="tile">
                        <span>Deaths</span>
                        <input type="number" name="deaths" readonly>
                    </div>

                    <div class="tile">
                        <span>Absent</span>
                        <input type="number" name="absent">
                    </div>

                    <div class="tile">
                        <span>Sick</span>
                        <input type="number" name="ab_sick">
                    </div>

                    <div class="tile">
                        <span>Limping</span>
                        <input type="number" name="ab_limbing">
                    </div>

                    <div class="tile">
                        <span>Calving</span>
                        <input type="number" name="calving">
                    </div>

                    <div class="tile">
                        <span>Missing</span>
                        <input type="number" name="missing">
                    </div>

                    <div class="tile">
                        <span>Extras</span>
                        <input type="number" name="extras">
                    </div>

                </div>

                <!-- SUMMARY ROW -->
                <div class="win-tab-summary">

                    <div>
                        <label>On Master</label>
                        <input type="text" name="prev" readonly>
                    </div>

                    <div>
                        <label>On Register</label>
                        <input type="text" name="on_register" readonly>
                    </div>

                    <div>
                        <label>Dipped</label>
                        <input type="text" name="dipped" readonly>
                    </div>

                </div>

                <!-- ACTIONS -->
                <div class="win-tab-actions">

                    <button type="button" onclick="retrieveRecord()">Retrieve</button>
                    <button type="button">Generate</button>
                    <button type="button" class="primary">Save</button>

                </div>

            </form>

        </div>

    </div>

</div>

<div id="dailyRecordsResults" style="display:none;">

    <div class="search-expanded-header">

        <h3>
            Daily Records Search Results
        </h3>

    <div class="header-actions">

        <button
            type="button"
            class="header-icon-btn"
            onclick="printResults()"
            title="Print">

            <i class="fa-solid fa-print"></i>

        </button>

        <button
            type="button"
            class="header-icon-btn"
            onclick="exportPDF()"
            title="Export PDF">

            <i class="fa-solid fa-file-pdf"></i>

        </button>

        <button
            type="button"
            class="header-icon-btn"
            onclick="exportExcel()"
            title="Export Excel">

            <i class="fa-solid fa-file-excel"></i>

        </button>

        <button
            type="button"
            class="close-expanded"
            onclick="closeTableViewFate()">

            ✕

        </button>
</div>
    </div>

    <div class="table-responsive">

        <div id="dailyRecordsTableContainer">

        </div>

    </div>

</div>
<script>

function printResults()
{
    let table =
        document.getElementById(
            "dailyRecordsTableContainer"
        ).innerHTML;

    let printWindow =
        window.open(
            "",
            "",
            "width=1200,height=800"
        );

    printWindow.document.write(`
    <html>
    <head>

        <div class="report-header">

    <img
src="https://www.clipartmax.com/png/full/340-3408862_knight-slaying-dragon-clipart.png"
class="header-logo">
    <div class="ministry-title">

        <strong>
            Ministry of Agriculture
        </strong>

        <br>

        <strong>
            Department of Veterinary Services
        </strong>

    </div>

    <div class="report-title">

        DAILY CATTLE RECORDS

        <br>

        AS AT

        <br>

        ${new Date().toLocaleString(
            'default',
            {
                month:'long',
                year:'numeric'
            }
        ).toUpperCase()}

    </div>

</div>
        <style>
.report-header{

    text-align:center;

    margin-bottom:20px;
}

.header-logo{

    width:90px;

    height:auto;

    margin-bottom:10px;
}

.ministry-title{

    font-size:18px;

    margin-bottom:15px;
}

.report-title{

    font-size:20px;

    font-weight:bold;

    text-transform:uppercase;
}

            body{

                font-family:Arial,sans-serif;

                margin:20px;

                color:#000;
            }

            h2{

                text-align:center;

                margin-bottom:5px;
            }

            .report-date{

                text-align:center;

                margin-bottom:20px;

                color:#555;
            }

            table{

                width:100%;

                border-collapse:collapse;

                font-size:12px;
            }

            th{

                background:#38bdf8;

                color:#000;

                border:1px solid #999;

                padding:8px;

                text-align:center;
            }

            td{

                border:1px solid #999;

                padding:6px;

                text-align:center;
            }

            tr:nth-child(even){

                background:#f8fafc;
            }

            .footer{

                margin-top:20px;

                text-align:right;

                font-size:11px;

                color:#666;
            }

        </style>

    </head>

    <body>

        <h2>
            Daily Records Search Report
        </h2>

        <div class="report-date">

            Generated on:
            ${new Date().toLocaleString()}

        </div>

        ${table}

        <div class="footer">

            Livestock Registers System

        </div>

    </body>

    </html>
    `);

    printWindow.document.close();

    printWindow.focus();

    setTimeout(function(){

        printWindow.print();

    },500);
}

</script>
<div id="birthPopup" class="win-tab-overlay" style="display:none;">

    <div class="win-tab-window">

        <!-- HEADER -->
        <div class="win-tab-header">

            <div class="win-tab-title">
                Register Birth
            </div>

            <button class="win-tab-close" onclick="closeBirthPopup()">✕</button>

        </div>

        <!-- BODY -->
        <div class="win-tab-body">

            <form id="birthForm">

                <!-- TOP ROW -->
                <div class="win-tab-top">

                    <div class="win-field">
                        <label>Diptank</label>
                        <input type="text" name="diptank_no" required>
                    </div>

                    <div class="win-field">
                        <label>Kraal</label>
                        <input type="text" name="kraal_no" required>
                    </div>

                    <div class="win-field">
                        <label>Date</label>
                        <input type="date" name="date" value="2026-07-05">
                    </div>

                </div>

                <!-- MID GRID (SELECTS) -->
                <div class="win-tab-grid">

                    <div class="tile">
                        <span>Tenure</span>
                        <select name="tenure">
                            <option>SNL</option>
                            <option>TDL</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Sex</span>
                        <select name="sex">
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Purpose</span>
                        <select name="purpose">
                            <option>Beef</option>
                            <option>Dairy</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Color</span>
                        <select name="color">
                            <option>Red</option>
                            <option>Black</option>
                            <option>White</option>
                            <option>Brown</option>
                            <option>Red and White</option>
                            <option>Black and White</option>
                            <option>Brown and White</option>
                            <option>Grey</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Breed</span>
                        <select name="breed">
                            <option>Nguni</option>
                            <option>Nguni X</option>
                            <option>Brahman</option>
                            <option>Jersey</option>
                            <option>Holstein</option>
                            <option>Bonsmara</option>
                            <option>Angus</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Dam ID</span>
                        <input type="text" name="dam_id" required>
                    </div>

                </div>

                <!-- ACTIONS -->
                <div class="win-tab-actions">

                    <button type="submit" class="primary">
                        <i class="fa fa-save"></i> Submit Birth
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
<div id="permitInPopup" class="win-tab-overlay" style="display:none;">

    <div class="win-tab-window">

        <!-- HEADER -->
        <div class="win-tab-header">

            <div class="win-tab-title">
                Create Permit In
            </div>

            <button class="win-tab-close" onclick="closePermitInPopup()">✕</button>

        </div>

        <!-- BODY -->
        <div class="win-tab-body">

            <form id="permitInForm">

                <!-- TOP INFO -->
                <div class="win-tab-top">

                    <div class="win-field">
                        <label>Diptank</label>
                        <input type="text" name="diptank_no" required>
                    </div>

                    <div class="win-field">
                        <label>Kraal</label>
                        <input type="text" name="kraal_no" required>
                    </div>

                    <div class="win-field">
                        <label>Birth Date</label>
                        <input type="date" name="birth_date" value="2026-07-05">
                    </div>

                </div>

                <!-- MAIN GRID -->
                <div class="win-tab-grid">

                    <div class="tile">
                        <span>Tenure</span>
                        <select name="tenure">
                            <option>SNL</option>
                            <option>TDL</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Sex</span>
                        <select name="sex">
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Purpose</span>
                        <select name="purpose">
                            <option>Beef</option>
                            <option>Dairy</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Color</span>
                        <select name="color">
                            <option>Red</option>
                            <option>Black</option>
                            <option>White</option>
                            <option>Brown</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Breed</span>
                        <select name="breed">
                            <option>Nguni</option>
                            <option>Brahman</option>
                            <option>Jersey</option>
                            <option>Angus</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Animal ID</span>
                        <input type="text" name="animal_id" required>
                    </div>

                    <div class="tile">
                        <span>Permit No</span>
                        <input type="text" name="permit_no" required>
                    </div>

                    <div class="tile">
                        <span>From Diptank</span>
                        <input type="text" name="fdiptank_no" required>
                    </div>

                    <div class="tile">
                        <span>From Kraal</span>
                        <input type="text" name="fkraal_no" required>
                    </div>

                    <div class="tile">
                        <span>Registration</span>
                        <input type="date" name="registration_date" value="2026-07-05">
                    </div>

                </div>

                <!-- ACTIONS -->
                <div class="win-tab-actions">

                    <button type="submit" class="primary">
                        Submit Permit
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<div id="birthHideConfirmPopup" class="inner-popup" style="display:none;">

    <div class="inner-popup-box">

        <div class="popup-header">
            Confirm Action
        </div>

        <div class="popup-body">

            <div class="popup-icon warning-icon">
                ⚠
            </div>

            Are you sure you want to hide the selected birth records?

        </div>

        <div style="display:flex;gap:10px;justify-content:center;padding:15px;">

            <button
                class="popup-btn"
                onclick="confirmBirthHideSelected()">

                Delete

            </button>

            <button
                class="popup-btn"
                onclick="closeBirthHideConfirmPopup()">

                Cancel

            </button>

        </div>

    </div>

</div>

                <div id="fatePopup" class="fate-popup" style="display:none;">

    <div class="fate-popup-box">

       <div class="fate-header">

    <div class="fate-title">
        <i class="fa-solid fa-database"></i>
        Fates Register
    </div>

    <div class="fate-header-actions">

        <input
    type="text"
    id="fateSearch"
    class="fate-search-input"
    placeholder="Search"
    onkeyup="searchFateTable()">

        <button
            type="button"
            class="fate-search-btn"
            onclick="searchFateTable()">

            🔍

        </button>

        <button
            type="button"
            class="fate-close-btn"
            onclick="closeFatePopup()">

            ✕

        </button>

    </div>

</div>
        <div class="fate-content">

            <div class="records-table-wrapper">

                <table class="fate-table">

                    <thead>

                        <tr>

                            <th>Animal ID</th>
                            <th>Fate</th>
                            <th>Reference</th>
                            <th>Diptank Number</th>

                        </tr>

                    </thead>

                    <tbody id="fateRecords">


                        <tr>

                            <td>445-223</td>
                            <td></td>
                            <td>Died</td>
                            <td>444</td>

                        </tr>


                        <tr>

                            <td>434-1221</td>
                            <td>Permitted Out</td>
                            <td>098763</td>
                            <td>434</td>

                        </tr>


                        <tr>

                            <td>426-2122</td>
                            <td>Permitted Out</td>
                            <td>987011</td>
                            <td>0</td>

                        </tr>


                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<div id="requestEartagPopup" class="popup-overlay">
 <div class="fate-popup-box">

       <div class="fate-header">

    <div class="fate-title">
        <i class="fa fa-tag"></i>
        Request Eartags
    </div>

    <div class="fate-header-actions">

        <input
    type="text"
    id="eartagSearch"
    class="fate-search-input"
    placeholder="Search"
    onkeyup="searchEartagTable()">

        <button
            type="button"
            class="fate-search-btn"
            onclick="">

            🔍

        </button>

        <button
            type="button"
            class="fate-close-btn"
            onclick="closeFatePopup()">

            ✕

        </button>

    </div>

</div>
        <div class="fate-content">

<div class="records-table-wrapper">

            <table class="records-table">

                <thead>
                    <tr>
                      
			<th>Select</th>
                        <th>Kraal No.</th>
                        <th>Owner Name</th>
                        <th>Dam ID</th>
                        <th>Sex</th>
                        <th>Color</th>
                        <th>Date of Birth</th>
                    </tr>
                </thead>

                <tbody id="eartagRequestBody">

                </tbody>

            </table>

        </div>

    </div>

    <div class="record-actions">

        <button type="button"
                class="record-btn"
                onclick="generateTagRequest();">

            Request

        </button>

        <button type="button"
                class="record-btn"
                onclick="closeRequestEartagPopup();">

            Close

        </button>

    </div>

</div>
</div>
<div id="applyEartagPopup" class="popup-overlay">

<div class="fate-popup-box">

    <div class="fate-header">

        <div class="fate-title">

            <i class="fa fa-tags"></i>
            Apply Eartags

        </div>

        <div class="fate-header-actions">

            <input
                type="text"
                id="applyTagSearch"
                class="fate-search-input"
                placeholder="Search Eartag"
                onkeyup="searchApplyEartagTable()">

            <button
                type="button"
                class="fate-search-btn">

                🔍

            </button>

            <button
                type="button"
                class="fate-close-btn"
                onclick="closeApplyEartagPopup()">

                ✕

            </button>

        </div>

    </div>

    <div class="fate-content">

        <div class="records-table-wrapper">

            <table class="records-table">

                <thead>

                    <tr>

                        <th class="select-col">

                            <input
                                type="checkbox"
                                id="selectAllApplyTags">

                        </th>
			
                        <th>Select</th>

                        <th>Eartag Number</th>
                        <th>Dam ID</th>
                        <th>Kraal No.</th>
                        <th>Owner Name</th>
                        <th>Sex</th>
                        <th>Date of Birth</th>

                    </tr>

                </thead>

                <tbody id="applyEartagBody">

                </tbody>

            </table>

        </div>

    </div>

    <div class="record-actions">

        <button
            type="button"
            class="record-btn"
            onclick="applySelectedEartags();">

            Apply Selected

        </button>

        <button
            type="button"
            class="record-btn"
            onclick="closeApplyEartagPopup();">

            Close

        </button>

    </div>

</div>

</div>
<div class="popup-overlay" id="damErrorPopup">

    <div class="popup-box popup-error">

        <div class="popup-body">

            <div class="popup-icon error-icon">✖</div>

            Dam does not exist.

        </div>

        <div class="popup-footer">

            <button class="popup-btn" onclick="closeDamError()">
                CLOSE
            </button>

        </div>

    </div>

</div>
<div id="permitOutPopup" class="win-tab-overlay" style="display:none;">

    <div class="win-tab-window">

        <!-- HEADER -->
        <div class="win-tab-header">

            <div class="win-tab-title">
                Permit Out
            </div>

            <button class="win-tab-close" onclick="closePermitOutPopup()">✕</button>

        </div>

        <!-- BODY -->
        <div class="win-tab-body">

            <form id="permitOutForm">

                <!-- TOP ROW -->
                <div class="win-tab-top">

                    <div class="win-field">
                        <label>Diptank</label>
                        <input type="text" name="diptank_no" required>
                    </div>

                    <div class="win-field">
                        <label>Kraal</label>
                        <input type="text" name="kraal_no" required>
                    </div>

                    <div class="win-field">
                        <label>Birth Date</label>
                        <input type="date" name="birth_date" value="2026-07-05">
                    </div>

                </div>

                <!-- MAIN GRID -->
                <div class="win-tab-grid">

                    <div class="tile">
                        <span>Tenure</span>
                        <select name="tenure">
                            <option>SNL</option>
                            <option>TDL</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Sex</span>
                        <select name="sex">
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Purpose</span>
                        <select name="purpose">
                            <option>Beef</option>
                            <option>Dairy</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Color</span>
                        <select name="color">
                            <option>Red</option>
                            <option>Black</option>
                            <option>White</option>
                            <option>Brown</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Breed</span>
                        <select name="breed">
                            <option>Nguni</option>
                            <option>Brahman</option>
                            <option>Jersey</option>
                            <option>Angus</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Registration</span>
                        <input type="date" name="registration_date" value="2026-07-05">
                    </div>

                    <div class="tile">
                        <span>Animal ID</span>
                        <input type="text" name="animal_id" required>
                    </div>

                    <div class="tile">
                        <span>Permit No</span>
                        <input type="text" name="permit_no" required>
                    </div>

                    <div class="tile">
                        <span>To Diptank</span>
                        <input type="text" name="tdiptank_no" required>
                    </div>

                    <div class="tile">
                        <span>To Kraal</span>
                        <input type="text" name="tkraal_no" required>
                    </div>

                </div>

                <!-- ACTIONS -->
                <div class="win-tab-actions">

                    <button type="submit" class="primary">
                        Submit Permit Out
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
<div id="deathPopup" class="win-tab-overlay" style="display:none;">

    <div class="win-tab-window">

        <!-- HEADER -->
        <div class="win-tab-header">

            <div class="win-tab-title">
                Create Death Record
            </div>

            <button class="win-tab-close" onclick="closeDeathPopup()">✕</button>

        </div>

        <!-- BODY -->
        <div class="win-tab-body">

            <form id="deathForm">

                <!-- TOP ROW -->
                <div class="win-tab-top">

                    <div class="win-field">
                        <label>Diptank</label>
                        <input type="text" name="diptank_no" required>
                    </div>

                    <div class="win-field">
                        <label>Kraal</label>
                        <input type="text" name="kraal_no" required>
                    </div>

                    <div class="win-field">
                        <label>Tenure</label>
                        <select name="tenure">
                            <option>SNL</option>
                            <option>TDL</option>
                        </select>
                    </div>

                </div>

                <!-- MAIN GRID -->
                <div class="win-tab-grid">

                    <div class="tile">
                        <span>Registration</span>
                        <input type="date" name="date" value="2026-07-05">
                    </div>

                    <div class="tile">
                        <span>Animal ID</span>
                        <input type="text" name="animal_id" required>
                    </div>

                    <div class="tile">
                        <span>Purpose</span>
                        <select name="purpose">
                            <option>Beef</option>
                            <option>Dairy</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Reason</span>
                        <select name="reason">
                            <option>Died</option>
                            <option>Home Slaughter</option>
                            <option>Butchery Slaughtered</option>
                            <option>Killed</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Suspected Disease</span>
                        <select name="suspect">
                            <option>None</option>
                            <option>Anthrax</option>
                            <option>Foot and Mouth</option>
                            <option>Anaplasmosis</option>
                            <option>Crowdosis</option>
                            <option>Lumpy Skin</option>
                            <option>Mastitis</option>
                            <option>Rabies</option>
                            <option>Scours</option>
                            <option>Viral Pneumonia</option>
                            <option>Ephemeral Fever</option>
                            <option>Clostridium</option>
                            <option>Papillomatosis</option>
                            <option>Corona Viral</option>
                            <option>Other</option>
                        </select>
                    </div>

                    <div class="tile">
                        <span>Death Date</span>
                        <input type="date" name="death_date" value="2026-07-05">
                    </div>

                </div>

                <!-- ACTIONS -->
                <div class="win-tab-actions">

                    <button type="submit" class="primary">
                        Submit Death
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
<div id="permitInHideConfirmPopup"
     class="inner-popup"
     style="display:none;">

    <div class="inner-popup-box">

        <div class="popup-header">

            Confirm Action

        </div>

        <div class="popup-body">

            <div class="popup-icon warning-icon">

                ⚠

            </div>

            Are you sure you want to hide the selected Permit In records?

        </div>

        <div
            style="display:flex;
                   gap:10px;
                   justify-content:center;
                   padding:15px;">

            <button
                class="popup-btn"
                onclick="confirmPermitInHideSelected()">

                Hide

            </button>

            <button
                class="popup-btn"
                onclick="closePermitInHideConfirmPopup()">

                Cancel

            </button>

        </div>

    </div>

</div>
<div id="permitOutHideConfirmPopup"
     class="inner-popup"
     style="display:none;">

    <div class="inner-popup-box">

        <div class="popup-header">

            Confirm Action

        </div>

        <div class="popup-body">

            <div class="popup-icon warning-icon">

                ⚠

            </div>

            Are you sure you want to hide the selected Permit Out records?

        </div>

        <div style="display:flex;gap:10px;justify-content:center;padding:15px;">

            <button
                class="popup-btn"
                onclick="confirmPermitOutHideSelected()">

                Hide

            </button>

            <button
                class="popup-btn"
                onclick="closePermitOutHideConfirmPopup()">

                Cancel

            </button>

        </div>

    </div>

</div>
<div id="deathHideConfirmPopup"
     class="inner-popup"
     style="display:none;">

    <div class="inner-popup-box">

        <div class="popup-header">
            Confirm Action
        </div>

        <div class="popup-body">

            <div class="popup-icon warning-icon">
                ⚠
            </div>

            Are you sure you want to hide the selected Death records?

        </div>

        <div style="display:flex;gap:10px;justify-content:center;padding:15px;">

            <button
                class="popup-btn"
                onclick="confirmDeathHideSelected()">

                Hide

            </button>

            <button
                class="popup-btn"
                onclick="closeDeathHideConfirmPopup()">

                Cancel

            </button>

        </div>

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

    <div class="new-message-users">
<?php

include_once "config.php";

$currentUser = $_SESSION["id"];

$sql = "SELECT id, name, surname
        FROM users
        WHERE id != $currentUser
        ORDER BY name ASC";

$result = mysqli_query($link, $sql);

if(mysqli_num_rows($result) > 0){

    while($user = mysqli_fetch_assoc($result)){
?>

        <div class="user-item"
            onclick="openConversation( <?php echo $user['id']; ?>,'<?php echo htmlspecialchars($user['name'].' '.$user['surname']); ?>'
)">

            <div class="user-avatar">
                <i class="fa-solid fa-user"></i>
            </div>

            <div class="user-name">
                <?php
                echo htmlspecialchars($user['name']) . " " .
                     htmlspecialchars($user['surname']);
                ?>
            </div>

        </div>

<?php
    }

}else{

    echo "<div style='padding:15px;'>No users found</div>";

}
?>
    </div>

</div>
<script>
function updatePresence() {
    fetch('update_status.php', {
        method: 'POST',
        credentials: 'include'
    });
}

// send every 20 seconds
setInterval(updatePresence, 20000); // Correct interval

// also send immediately on page load
updatePresence();

setInterval(() => {
    fetch('presence_cleanup.php');
}, 60000);
</script>
<script>
/* =========================================================
   TAB SYSTEM (SINGLE SOURCE OF TRUTH)
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    const tabs = document.querySelectorAll(".tab-btn");
    const contents = document.querySelectorAll(".tab-content");

    function activateTab(targetId) {

        tabs.forEach(btn => btn.classList.remove("active"));
        contents.forEach(tab => tab.classList.remove("active"));

        const targetBtn = document.querySelector(`[data-tab="${targetId}"]`);
        const targetTab = document.getElementById(targetId);

        if (targetBtn) targetBtn.classList.add("active");
        if (targetTab) targetTab.classList.add("active");
    }

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            activateTab(tab.getAttribute("data-tab"));
        });
    });

    // URL TAB SUPPORT
    const params = new URLSearchParams(window.location.search);
    const activeTab = params.get("tab");

    if (activeTab) {
        activateTab(activeTab);
    } else if (tabs.length) {
        activateTab(tabs[0].getAttribute("data-tab"));
    }

});
</script>


<script>
/* =========================================================
   POPUPS (CLEAN + SINGLE CONTROL)
========================================================= */

function showSuccessPopup() {
    document.getElementById("loadingPopup").style.display = "none";
    document.getElementById("successPopup").style.display = "flex";
    if (typeof refreshAllTables === "function") refreshAllTables();
}

function showErrorPopup() {
    document.getElementById("loadingPopup").style.display = "none";
    document.getElementById("errorPopup").style.display = "flex";
}

function closeSuccessPopup() {
    document.getElementById("successPopup").style.display = "none";
}

function closeErrorPopup() {
    document.getElementById("errorPopup").style.display = "none";
}

function closeExistsPopup() {
    document.getElementById("existsPopup").style.display = "none";
}

function closeDuplicatePopup() {
    document.getElementById("duplicatePopup").style.display = "none";
}

function closeNotFoundPopup() {
    document.getElementById("notFoundPopup").style.display = "none";
}
</script>


<script>
/* =========================================================
   DEATH FORM
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    const deathForm = document.getElementById("deathForm");

    if (!deathForm) return;

    deathForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        document.getElementById("loadingPopup").style.display = "flex";

        try {

            const res = await fetch("contact_death.php", {
                method: "POST",
                body: new FormData(deathForm)
            });

            const data = (await res.text()).trim();

            document.getElementById("loadingPopup").style.display = "none";

            if (data === "success") {

                document.getElementById("successPopup").style.display = "flex";
                deathForm.reset();

                refreshAllRegisters?.();
                loadDeathRecords();

                activateTab("deaths");

            } else {
                document.getElementById("errorPopup").style.display = "flex";
            }

        } catch (err) {
            document.getElementById("loadingPopup").style.display = "none";
            document.getElementById("errorPopup").style.display = "flex";
        }

    });

});

function loadDeathRecords() {
    fetch("fetch_deaths.php")
        .then(res => res.text())
        .then(data => {
            document.getElementById("deathRecords").innerHTML = data;
        });
}
</script>


<script>
/* =========================================================
   BIRTH FORM
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    const birthForm = document.getElementById("birthForm");
    if (!birthForm) return;

    birthForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        document.getElementById("loadingPopup").style.display = "flex";

        try {

            const res = await fetch("contact4.php", {
                method: "POST",
                body: new FormData(birthForm)
            });

            const data = (await res.text()).trim();

            document.getElementById("loadingPopup").style.display = "none";

            if (data === "success") {

                document.getElementById("successPopup").style.display = "flex";
                birthForm.reset();

                refreshAllRegisters?.();
                loadBirthRecords();

                activateTab("births");

            } else {
                document.getElementById("errorPopup").style.display = "flex";
            }

        } catch (err) {
            document.getElementById("loadingPopup").style.display = "none";
            document.getElementById("errorPopup").style.display = "flex";
        }

    });

});



function loadBirthRecords()
{
    fetch("fetch_births.php")

    .then(response => response.text())

    .then(data => {

        document.getElementById(
            "birthRecords"
        ).innerHTML = data;

    })

    .catch(error => {

        console.error(error);

    });
}

/* Load records when page opens */
window.addEventListener("load", loadBirthRecords);

</script>


<script>
/* =========================================================
   PERMIT OUT FORM
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    const permitOutForm = document.getElementById("permitOutForm");

    if (!permitOutForm) return;

    permitOutForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        document.getElementById("loadingPopup").style.display = "flex";

        try {

            const res = await fetch("contact7.php", {
                method: "POST",
                body: new FormData(permitOutForm)
            });

            const data = (await res.text()).trim();

            document.getElementById("loadingPopup").style.display = "none";

            if (data === "success") {

                document.getElementById("successPopup").style.display = "flex";
                permitOutForm.reset();

                loadPermitOutRecords();
                refreshAllRegisters?.();

                activateTab("permitsout");

            } else if (data === "duplicate") {

                document.getElementById("duplicatePopup").style.display = "flex";

            } else if (data === "notfound") {

                document.getElementById("notFoundPopup").style.display = "flex";

            } else {

                document.getElementById("errorPopup").style.display = "flex";

            }

        } catch (err) {
            document.getElementById("loadingPopup").style.display = "none";
            document.getElementById("errorPopup").style.display = "flex";
        }

    });

});

function loadPermitOutRecords() {
    fetch("fetch_permits_out.php")
        .then(res => res.text())
        .then(data => {
            document.getElementById("permitOutRecords").innerHTML = data;
        });
}
</script>


<script>
/* =========================================================
   ANIMAL PROFILE SYSTEM
========================================================= */

let currentAnimal = {};

function openAnimalPopup(
    id, animal_id, birth_date, sex, breed,
    color, kraal, diptank, registration,
    source, alterations, Tenure
) {

    currentAnimal = {
        id, animal_id, birth_date, sex, breed,
        color, kraal, diptank, registration,
        source, alterations, Tenure
    };

    document.getElementById("profileAnimalID").innerText = animal_id;
    document.getElementById("profileBirthDate").innerText = birth_date;
    document.getElementById("profileSex").innerText = sex;
    document.getElementById("profileBreed").innerText = breed;
    document.getElementById("profileColor").innerText = color;
    document.getElementById("profileKraal").innerText = kraal;
    document.getElementById("profileDiptank").innerText = diptank;
    document.getElementById("profileTenure").innerText = Tenure;
    document.getElementById("profileSource").innerText = source;
    document.getElementById("profileAlterations").innerText = alterations;

    document.getElementById("animalProfilePopup").style.display = "flex";
}

function closeAnimalPopup() {
    document.getElementById("animalProfilePopup").style.display = "none";
}

document.addEventListener("click", (e) => {

    if (e.target.id === "editAnimalBtn") {

        document.getElementById("editID").value = currentAnimal.id;
        document.getElementById("editAnimalID").value = currentAnimal.animal_id;
        document.getElementById("editBirthDate").value = currentAnimal.birth_date;
        document.getElementById("editSex").value = currentAnimal.sex;
        document.getElementById("editBreed").value = currentAnimal.breed;
        document.getElementById("editColor").value = currentAnimal.color;
        document.getElementById("editKraal").value = currentAnimal.kraal;
        document.getElementById("editDiptank").value = currentAnimal.diptank;
        document.getElementById("editTenure").value = currentAnimal.Tenure;
        document.getElementById("editSource").value = currentAnimal.source;
        document.getElementById("editAlterations").value = currentAnimal.alterations;

        document.getElementById("editAnimalPopup").style.display = "flex";
    }

});

function closeEditAnimalPopup() {
    document.getElementById("editAnimalPopup").style.display = "none";
}

const medicalHistoryBtn = document.getElementById("medicalHistoryBtn");

if (medicalHistoryBtn) {
    medicalHistoryBtn.addEventListener("click", () => {
        window.location.href =
            "medical_history.php?animal_id=" + currentAnimal.animal_id;
    });
}
</script>
<script>
/* =========================================================
   GLOBAL HELPERS
========================================================= */

function show(el) {
    if (el) el.style.display = "flex";
}

function hide(el) {
    if (el) el.style.display = "none";
}

function get(id) {
    return document.getElementById(id);
}

/* =========================================================
   TAB SYSTEM
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    const tabs = document.querySelectorAll(".tab-btn");

    function switchTab(target) {

        document.querySelectorAll(".tab-btn").forEach(btn => {
            btn.classList.remove("active");
        });

        document.querySelectorAll(".tab-content").forEach(tab => {
            tab.classList.remove("active");
            hide(tab);
        });

        const activeTab = get(target);

        if (activeTab) {
            activeTab.classList.add("active");
            activeTab.style.display = "block";
        }

        const activeBtn = document.querySelector(`[data-tab="${target}"]`);
        if (activeBtn) activeBtn.classList.add("active");
    }

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            const target = tab.dataset.tab;
            if (target) switchTab(target);
        });
    });

});

/* =========================================================
   POPUPS
========================================================= */

function showSuccessPopup() {
    hide(get("loadingPopup"));
    show(get("successPopup"));
    if (typeof refreshAllTables === "function") refreshAllTables();
}

function showErrorPopup() {
    hide(get("loadingPopup"));
    show(get("errorPopup"));
}

function closeSuccessPopup() {
    hide(get("successPopup"));
}

function closeErrorPopup() {
    hide(get("errorPopup"));
}

/* =========================================================
   DEATH FORM
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    const form = get("deathForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        show(get("loadingPopup"));

        try {

            const res = await fetch("contact_death.php", {
                method: "POST",
                body: new FormData(form)
            });

            const data = (await res.text()).trim();

            hide(get("loadingPopup"));

            if (data === "success") {

                show(get("successPopup"));
                form.reset();

                if (typeof refreshAllRegisters === "function") refreshAllRegisters();
                if (typeof loadDeathRecords === "function") loadDeathRecords();

            } else {
                show(get("errorPopup"));
            }

        } catch (err) {
            hide(get("loadingPopup"));
            show(get("errorPopup"));
        }

    });

});

/* =========================================================
   BIRTH FORM
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    const form = get("birthForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        show(get("loadingPopup"));

        try {

            const res = await fetch("contact4.php", {
                method: "POST",
                body: new FormData(form)
            });

            const data = (await res.text()).trim();

            hide(get("loadingPopup"));

            if (data === "success") {

                show(get("successPopup"));
                form.reset();

                if (typeof refreshAllRegisters === "function") refreshAllRegisters();
                if (typeof loadBirthRecords === "function") loadBirthRecords();

            } else {
                show(get("errorPopup"));
            }

        } catch (err) {
            hide(get("loadingPopup"));
            show(get("errorPopup"));
        }

    });

});

/* =========================================================
   PERMIT IN FORM
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    const form = get("permitInForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        show(get("loadingPopup"));

        try {

            const res = await fetch("contact6.php", {
                method: "POST",
                body: new FormData(form)
            });

            const data = (await res.text()).trim();

            hide(get("loadingPopup"));

            if (data === "success") {

                show(get("successPopup"));
                form.reset();

                if (typeof refreshAllRegisters === "function") refreshAllRegisters();

                switchTab("permitsin");

            } else {
                show(get("errorPopup"));
            }

        } catch (err) {
            hide(get("loadingPopup"));
            show(get("errorPopup"));
        }

    });

});

/* =========================================================
   MASTER + FATE LOADERS
========================================================= */

function loadMasterRecords() {
    fetch("fetch_master_register.php")
        .then(r => r.text())
        .then(d => {
            const el = get("masterRecords");
            if (el) el.innerHTML = d;
        });
}

function loadFateRecords() {
    fetch("fetch_fate_register.php")
        .then(r => r.text())
        .then(d => {
            const el = get("fateRecords");
            if (el) el.innerHTML = d;
        });
}

function refreshAllRegisters() {
    loadMasterRecords();
    loadFateRecords();
}

/* =========================================================
   EDIT ANIMAL FORM
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    const form = get("editAnimalForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        try {

            const res = await fetch("update_animal.php", {
                method: "POST",
                body: new FormData(form)
            });

            const data = (await res.text()).trim();

            if (data === "success") {

                if (typeof closeEditAnimalPopup === "function") closeEditAnimalPopup();
                if (typeof closeAnimalPopup === "function") closeAnimalPopup();

                loadMasterRecords();
                loadFateRecords();

                show(get("successPopup"));

            } else {
                alert("Server returned: " + data);
            }

        } catch (err) {
            alert("Update failed");
        }

    });

});

/* =========================================================
   PAGE LOADER
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll("a").forEach(link => {

        link.addEventListener("click", () => {

            const href = link.getAttribute("href");

            if (href && href !== "#" && !href.startsWith("javascript:")) {
                show(get("pageLoader"));
            }

        });

    });

});

window.addEventListener("load", () => {
    hide(get("pageLoader"));
});

/* =========================================================
   MESSAGES PANEL
========================================================= */

function toggleMessagesPanel() {

    const panel = get("messagesPanel");

    if (!panel) return;

    panel.classList.toggle("active");

    if (typeof savePanelState === "function") {
        savePanelState();
    }
}
</script>
<script>


/* =========================================================
   NOTES VIEWER
========================================================= */

function viewNote(title, content, date) {

    const t = get("viewNoteTitle");
    const c = get("viewNoteContent");
    const d = get("viewNoteDate");
    const popup = get("viewNotePopup");

    if (t) t.innerText = title;
    if (c) c.innerText = content;
    if (d) d.innerText = "Created: " + date;

    if (popup) popup.style.display = "flex";
}

function closeNoteViewer() {
    const popup = get("viewNotePopup");
    if (popup) popup.style.display = "none";
}
</script>
<script>
/* =========================================================
   HELPERS
========================================================= */

const get = (id) => document.getElementById(id);

const show = (el) => {
    if (el) el.classList.add("active");
};

const hide = (el) => {
    if (el) el.classList.remove("active");
};

/* =========================================================
   LOAD MESSAGES (SAFE + NON-OVERLAPPING)
========================================================= */

function loadMessages(userId) {

    const chatBody = get("chatBody");
    if (!chatBody) return;

    /* Cancel previous request */
    if (messageController) {
        messageController.abort();
    }

    messageController = new AbortController();

    fetch("get_messages.php?user_id=" + userId, {
        signal: messageController.signal
    })
    .then(r => r.text())
    .then(data => {

        const nearBottom =
            chatBody.scrollHeight -
            chatBody.scrollTop -
            chatBody.clientHeight < 50;

        chatBody.innerHTML = data;

        if (nearBottom) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }

    })
    .catch(err => {
        if (err.name !== "AbortError") {
            console.error("Message load error:", err);
        }
    });
}
/* =========================================================
   NOTES VIEWER
========================================================= */

function viewNote(title, content, date) {

    const t = get("viewNoteTitle");
    const c = get("viewNoteContent");
    const d = get("viewNoteDate");
    const popup = get("viewNotePopup");

    if (t) t.innerText = title;
    if (c) c.innerText = content;
    if (d) d.innerText = "Created: " + date;

    if (popup) popup.style.display = "flex";
}

function closeNoteViewer() {
    const popup = get("viewNotePopup");
    if (popup) popup.style.display = "none";
}
</script>
<script>
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

        // Mark announcements as read
        fetch("mark_announcements_read.php")
        .then(response => response.text())
        .then(data => {

            // Hide badge after opening announcements
            const badge =
                document.getElementById("announcementBadge");

            if(badge)
            {
                badge.style.display = "none";
                badge.innerHTML = "0";
            }

        });
    }
}
</script>
<script>
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
</script>
<script>

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
</script>
<script>
function updateUnreadMessages() {
    fetch("get_unread_count.php")
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
</script>
<script>

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

</script>
<script>
/* =========================================================
   LOADING + ERROR HANDLING
========================================================= */

function showLoad() {
    const el = document.getElementById("loadingPopup");
    if (el) el.style.display = "flex";
}

function closeError() {
    const el = document.getElementById("errorPopup");
    if (el) el.style.display = "none";
}

window.addEventListener("load", function () {

    const loading = document.getElementById("loadingPopup");
    const error = document.getElementById("errorPopup");

    if (loading) loading.style.display = "none";

    // Optional server-side flag (keep minimal PHP usage)
    
});

/* =========================================================
   INPUT VALIDATION
========================================================= */

document.addEventListener("input", function (e) {
    if (e.target.classList.contains("numeric-only")) {
        e.target.value = e.target.value.replace(/[^0-9]/g, "");
    }
});

/* =========================================================
   GENERIC TABLE TOGGLE SYSTEM (FIXED DUPLICATION ISSUE)
========================================================= */

function toggleTableView(id) {
    const el = document.getElementById(id);
    if (el) el.classList.toggle("expanded");
}

function closeTableView(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove("expanded");
}

/* =========================================================
   WRAPPER FUNCTIONS (for backward compatibility)
========================================================= */

function toggleTableViewBirths() {
    toggleTableView("tableContainer2");
}

function toggleTableViewPermitIn() {
    toggleTableView("tableContainer3");
}

function toggleTableViewPermitOut() {
    toggleTableView("tableContainer4");
}

function toggleTableViewDeath() {
    toggleTableView("tableContainer5");
}

function toggleTableViewMaster() {
    toggleTableView("tableContainer6");
}

function toggleTableViewFate() {
    toggleTableView("tableContainer7");
}

/* CLOSE WRAPPERS */

function closeTableViewBirths() {
    closeTableView("tableContainer2");
}

function closeTableViewPermitIn() {
    closeTableView("tableContainer3");
}

function closeTableViewPermitOut() {
    closeTableView("tableContainer4");
}

function closeTableViewDeath() {
    closeTableView("tableContainer5");
}

function closeTableViewMaster() {
    closeTableView("tableContainer6");
}

function closeTableViewFate() {
    closeTableView("tableContainer7");
}

/* =========================================================
   TRANSFER POPUP
========================================================= */

function openTransferPopup() {
    const el = document.getElementById("transferPopup");
    if (el) el.style.display = "flex";
}
</script>
<script>
/* =========================================================
   LOADING & ERROR HELPERS
========================================================= */

function showLoad() {
    const el = document.getElementById("loadingPopup");
    if (el) el.style.display = "flex";
}

function hideLoad() {
    const el = document.getElementById("loadingPopup");
    if (el) el.style.display = "none";
}

function closeErrorPopup() {
    const el = document.getElementById("errorPopup");
    if (el) el.style.display = "none";
}

/* =========================================================
   RECORD RETRIEVAL
========================================================= */

function retrieveRecord() {

    showLoad();

    const startTime = Date.now();
    const form = document.getElementById("dailyRecordForm");

    if (!form) {
        hideLoad();
        return;
    }

    const formData = new FormData(form);

    fetch("retrieve_record.php", {
        method: "POST",
        body: formData
    })

    .then(r => r.json())

    .then(data => {

        const prevField = document.querySelector('input[name="prev"]');
        const registerField = document.querySelector('input[name="on_register"]');
        const dippedField = document.querySelector('input[name="dipped"]');

        if (prevField) prevField.value = data.om ?? "";
        if (registerField) registerField.value = data.or ?? "";
        if (dippedField) dippedField.value = data.dip ?? "";

        const elapsed = Date.now() - startTime;
        const remaining = Math.max(0, 2000 - elapsed);

        setTimeout(hideLoad, remaining);
    })

    .catch(err => {

        console.error(err);

        const elapsed = Date.now() - startTime;
        const remaining = Math.max(0, 2000 - elapsed);

        setTimeout(() => {
            hideLoad();
            showRecordErrorPopup();
        }, remaining);
    });
}

/* =========================================================
   POPUPS
========================================================= */

function openRecordPopup() {
    const el = document.getElementById("recordPopup");
    if (el) el.style.display = "flex";
}

function closeRecordPopup() {
    const el = document.getElementById("recordPopup");
    if (el) el.style.display = "none";
}

function showRecordErrorPopup() {
    const el = document.getElementById("recordErrorPopup");
    if (el) el.style.display = "flex";
}

function closeRecordErrorPopup() {
    const el = document.getElementById("recordErrorPopup");
    if (el) el.style.display = "none";
}

/* =========================================================
   INPUT UX IMPROVEMENTS
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("dailyRecordForm");
    if (!form) return;

    /* Select 0 values on focus */
    form.querySelectorAll("input").forEach(input => {
        input.addEventListener("focus", function () {
            if (["0", "00", "000"].includes(this.value)) {
                this.select();
            }
        });
    });

    /* Apply default values on blur */
    form.querySelectorAll("input[data-default]").forEach(input => {
        input.addEventListener("blur", function () {
            if (this.value.trim() === "") {
                this.value = this.dataset.default;
            }
        });
    });
});

/* =========================================================
   SEARCH RECORDS
========================================================= */
function toggleDeleteMode()
{
    const bar =
        document.getElementById("deleteBar");

    const container =
        document.getElementById("tableContainer");

    const selectCols =
        container.querySelectorAll(".select-col");

    if (bar.classList.contains("show"))
    {
        // Hide delete bar
        bar.classList.remove("show");

        // Remove delete mode
        container.classList.remove("delete-mode");

        // Hide Select column
        selectCols.forEach(function(col){
            col.style.display = "none";
        });

        // Uncheck Mark All
        const selectAll =
            document.getElementById("selectAll");

        if(selectAll){
            selectAll.checked = false;
        }

        // Uncheck all row checkboxes
        container.querySelectorAll(".row-check")
            .forEach(function(box){
                box.checked = false;
            });
    }
    else
    {
        // Show delete bar
        bar.classList.add("show");

        // Enable delete mode
        container.classList.add("delete-mode");

        // Show Select column
        selectCols.forEach(function(col){
            col.style.display = "table-cell";
        });
    }
}

/* =============================
   SELECT / DESELECT ALL
============================= */

function toggleSelectAll(source)
{
    document.querySelectorAll(".row-check")
        .forEach(cb => cb.checked = source.checked);
}

function searchDailyRecords() {

    showLoad();

    const form = document.getElementById("searchDailyRecordsForm");

    if (!form) {
        hideLoad();
        return;
    }

    const formData = new FormData(form);

    fetch("search_daily_records.php", {
        method: "POST",
        body: formData
    })

    .then(r => r.text())

    .then(data => {

        const results = document.getElementById("dailyRecordsResults");
        const container = document.getElementById("dailyRecordsTableContainer");

        if (results) results.style.display = "block";
        if (container) container.innerHTML = data;

        hideLoad();
    })

    .catch(err => {
        hideLoad();
        console.error(err);
        alert("Failed to load records: " + err.message);
    });
}
</script>
<script>
/* =========================================================
   HELPERS
========================================================= */


function show(el) {
    if (el) el.style.display = "flex";
}

function hide(el) {
    if (el) el.style.display = "none";
}

/* =========================================================
   POPUPS (BIRTH)
========================================================= */

function openBirthPopup() {
    show(get("birthPopup"));
}

function closeBirthPopup() {
    hide(get("birthPopup"));
}
/* =============================
   MAIN TABLE DELETE MODE
============================= */

function toggleMainDeleteMode()
{
    const bar =
        document.getElementById("deleteBar");

    const container =
        document.getElementById("tableContainer");

    const selectCols =
        container.querySelectorAll(".select-col");

    if (bar.classList.contains("show"))
    {
        bar.classList.remove("show");

        container.classList.remove("delete-mode");

        selectCols.forEach(function(col){
            col.style.display = "none";
        });

        const selectAll =
            document.getElementById("selectAll");

        if(selectAll){
            selectAll.checked = false;
        }

        container.querySelectorAll(".row-check")
            .forEach(function(box){
                box.checked = false;
            });
    }
    else
    {
        bar.classList.add("show");

        container.classList.add("delete-mode");

        selectCols.forEach(function(col){
            col.style.display = "table-cell";
        });
    }
}



/* =============================
   BIRTH TABLE DELETE MODE
============================= */

function toggleBirthDeleteMode()
{
    const bar =
        document.getElementById("birthDeleteBar");

    const container =
        document.getElementById("tableContainer2");

    const selectCols =
        container.querySelectorAll(".select-col");

    if (bar.classList.contains("show"))
    {
        bar.classList.remove("show");

        container.classList.remove("delete-mode");

        selectCols.forEach(function(col){
            col.style.display = "none";
        });

        const birthSelectAll =
            document.getElementById("birthSelectAll");

        if(birthSelectAll){
            birthSelectAll.checked = false;
        }

        container.querySelectorAll(".birth-row-check")
            .forEach(function(box){
                box.checked = false;
            });
    }
    else
    {
        bar.classList.add("show");

        container.classList.add("delete-mode");

        selectCols.forEach(function(col){
            col.style.display = "table-cell";
        });
    }
}

/* =============================
   SELECT / DESELECT ALL BIRTHS
============================= */

function toggleBirthSelectAll(source)
{
    document.querySelectorAll(".birth-row-check")
        .forEach(cb => cb.checked = source.checked);
}

/* =========================================================
   HIDE SELECTED ROWS (MAIN TABLE)
========================================================= */

function hideSelectedRows() {

    const selectedIds = Array.from(
        document.querySelectorAll(".row-check:checked")
    ).map(cb => cb.dataset.id);

    if (selectedIds.length === 0) {
        show(get("noSelectionPopup"));
        return;
    }

    fetch("hide_selected.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ids: selectedIds })
    })

    .then(r => r.json())
    .then(data => {

        if (data.success) {

            show(get("hideSuccessPopup"));

            selectedIds.forEach(id => {
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) row.remove();
            });

        } else {
            alert(data.message || "Failed to hide records");
        }

    })

    .catch(err => {
        console.error(err);
        alert("An error occurred while hiding records.");
    });
}

/* =========================================================
   CONFIRM POPUPS
========================================================= */

let activeHideTab = null;

function openHideConfirmPopup() {

    const selected = document.querySelectorAll(".row-check:checked");

    if (!selected.length) {
        show(get("noSelectionPopup"));
        return;
    }

    activeHideTab = "cattle";
    show(get("hideConfirmPopup"));
}

function openBirthHideConfirmPopup() {

    const selected = document.querySelectorAll(".birth-row-check:checked");

    if (!selected.length) {
        show(get("noSelectionPopup"));
        return;
    }

    activeHideTab = "births";
    show(get("birthHideConfirmPopup"));
}

function confirmBirthHideSelected() {
    hide(get("birthHideConfirmPopup"));

    show(get("loadingPopup"));

    if (typeof hideBirthRecords === "function") {
        hideBirthRecords();
    }
}

/* =========================================================
   SUCCESS / RESET FLOW
========================================================= */

function openHideSuccessPopup() {
    show(get("hideSuccessPopup"));

    if (typeof refreshAllTables === "function") {
        refreshAllTables();
    }
}

function closeHideSuccessPopup() {

    hide(get("hideSuccessPopup"));

    const tab = sessionStorage.getItem("activeHideTab");

    if (!tab) return;

    localStorage.setItem("activeTab", tab);
    sessionStorage.removeItem("activeHideTab");

    if (typeof refreshTable === "function") {

        if (tab === "birthTab") {
            refreshTable("fetch_births.php", "birthRecords");
        }

        if (tab === "permitInTab") {
            refreshTable("fetch_permits_in.php", "permitInRecords");
        }

        if (tab === "deathTab") {
            refreshTable("fetch_deaths.php", "deathRecords");
        }

        if (tab === "masterTab") {
            refreshTable("fetch_master_register.php", "masterRegisterRecords");
        }
    }
}

/* =========================================================
   NO SELECTION POPUP
========================================================= */

function openNoSelectionPopup() {
    show(get("noSelectionPopup"));
}

function closeNoSelectionPopup() {
    hide(get("noSelectionPopup"));
}
</script>
<script>

/* =========================================================
   HIDE BIRTH RECORDS
========================================================= */

function hideBirthRecords() {

    const selectedIds = Array.from(
        document.querySelectorAll(".birth-row-check:checked")
    ).map(cb => cb.dataset.id);

    console.log("Selected IDs:", selectedIds);

    if (selectedIds.length === 0) {
        return;
    }

    fetch("hide_birth_records.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ ids: selectedIds })
    })

    .then(r => r.json())
    .then(data => {

        hide(get("loadingPopup"));

        if (data.success) {

            selectedIds.forEach(id => {
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) row.remove();
            });

            if (typeof openHideSuccessPopup === "function") {
                openHideSuccessPopup();
            }

        } else {
            alert(data.message || "Failed to hide records");
        }
    })

    .catch(err => {
        hide(get("loadingPopup"));
        console.error("Error:", err);
    });
}

/* =========================================================
   REFRESH
========================================================= */

function refreshBirthRecords() {

    fetch("fetch_births.php")
        .then(r => r.text())
        .then(html => {
            const el = get("birthRecords");
            if (el) el.innerHTML = html;
        })
        .catch(console.error);
}

/* =========================================================
   CONFIRM POPUP (FIXED DUPLICATE REMOVED)
========================================================= */

function openBirthHideConfirmPopup() {

    const selected = document.querySelectorAll(".birth-row-check:checked");

    if (!selected.length) {
        if (typeof openNoSelectionPopup === "function") {
            openNoSelectionPopup();
        }
        return;
    }

    show(get("birthHideConfirmPopup"));
}

function closeBirthHideConfirmPopup() {
    hide(get("birthHideConfirmPopup"));
}

/* =========================================================
   CONFIRM ACTION
========================================================= */

function confirmBirthHideSelected() {

    closeBirthHideConfirmPopup();
    show(get("loadingPopup"));

    hideBirthRecords();
}

/* =========================================================
   GLOBAL CONFIRM (MAIN TABLE)
========================================================= */

function confirmHideSelected() {

    const activeTab = document.querySelector(".tab-content.active");

    if (activeTab) {
        sessionStorage.setItem("activeHideTab", activeTab.id);
    }

    closeHideConfirmPopup();
    show(get("loadingPopup"));

    if (typeof hideSelectedRows === "function") {
        hideSelectedRows();
    }
}

/* =========================================================
   CLOSE MAIN CONFIRM
========================================================= */

function closeHideConfirmPopup() {
    hide(get("hideConfirmPopup"));
}
</script>
<script>
/* =========================================================
   SAFE HELPERS
========================================================= */

function show(el) {
    if (el) el.style.display = "flex";
}

function hide(el) {
    if (el) el.style.display = "none";
}

/* =========================================================
   FATE POPUP
========================================================= */

function openFatePopup() {
    show(get("fatePopup"));
}

function closeFatePopup() {
    hide(get("fatePopup"));
}

/* =========================================================
   FATE TABLE SEARCH
========================================================= */

function searchFateTable() {

    const input = get("fateSearch");
    if (!input) return;

    const filter = input.value.toUpperCase();

    document.querySelectorAll("#fateRecords tr").forEach(row => {

        const cell = row.cells?.[0];
        const animalId = cell ? cell.textContent.toUpperCase() : "";

        row.style.display = animalId.includes(filter) ? "" : "none";
    });
}

/* =========================================================
   PERMIT IN POPUP
========================================================= */

function openPermitInPopup() {
    show(get("permitInPopup"));
}

function closePermitInPopup() {
    hide(get("permitInPopup"));
}

/* =========================================================
   TAG MENU TOGGLE (SAFE VERSION)
========================================================= */

function toggleTagMenu() {
    const menu = get("tagMenu");
    if (menu) menu.classList.toggle("show");
}

/* CLOSE WHEN CLICKING OUTSIDE */
document.addEventListener("click", function (e) {

    const dropdown = document.querySelector(".tag-dropdown");
    const menu = get("tagMenu");

    if (!dropdown || !menu) return;

    if (!dropdown.contains(e.target)) {
        menu.classList.remove("show");
    }
});
</script>
<script>
/* =========================================================
   HELPERS
========================================================= */

function show(el) {
    if (el) el.style.display = "flex";
}

function hide(el) {
    if (el) el.style.display = "none";
}

/* =========================================================
   LOADING POPUPS
========================================================= */

function showLoadingPopup() {
    show(get("loadingPoup"));
}

function hideLoadingPopup() {
    hide(get("loadingPoup"));
}

/* =========================================================
   EARTAG REQUESTS
========================================================= */

function loadEartagRequests() {

    fetch("request_eartags.php")
        .then(res => res.text())
        .then(html => {

            const body = get("eartagRequestBody");
            if (body) body.innerHTML = html;

            show(get("requestEartagPopup"));

            const selectAll = get("selectAllTags");

            if (selectAll) {
                selectAll.onchange = function () {
                    document.querySelectorAll(".animal-checkbox")
                        .forEach(cb => cb.checked = selectAll.checked);
                };
            }

        })
        .catch(err => console.error("Eartag load error:", err));
}

function closeRequestEartagPopup() {
    hide(get("requestEartagPopup"));
}

/* =========================================================
   SEARCH EARTAGS
========================================================= */

function searchEartagTable() {

    const input = get("eartagSearch");
    const filter = input ? input.value.toUpperCase() : "";

    document.querySelectorAll("#eartagRequestBody tr")
        .forEach(row => {

            const text = row.textContent.toUpperCase();
            row.style.display = text.includes(filter) ? "" : "none";

        });
}

/* =========================================================
   GENERATE TAG REQUEST
========================================================= */

function generateTagRequest() {

    const checkboxes = document.querySelectorAll(".animal-checkbox");

    const selected = [];

    checkboxes.forEach(cb => {
        if (cb.checked) selected.push(cb.value);
    });

    if (selected.length === 0) {
        alert("Select at least one animal");
        return;
    }

    showLoadingPopup();

    fetch("save_eartag_request.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(selected)
    })
    .then(res => res.text())
    .then(msg => {

        hideLoadingPopup();

        alert(msg);

        // optional: reload only popup instead of full page
        loadEartagRequests();

    })
    .catch(err => {

        hideLoadingPopup();

        console.error("Save error:", err);

        alert("Error saving request");

    });
}
</script>
<script>
/* =========================================================
   HELPERS
========================================================= */

function show(el) {
    if (el) el.style.display = "flex";
}

function hide(el) {
    if (el) el.style.display = "none";
}

/* =========================================================
   LOADING
========================================================= */

function showLoadingPopup() {
    show(get("loadingPoup"));
}

function hideLoadingPopup() {
    hide(get("loadingPoup"));
}

/* =========================================================
   APPLY EARTAGS MODULE
========================================================= */

function loadApplyEartags() {

    fetch("load_approved_eartags.php")
        .then(res => res.text())
        .then(html => {

            const body = get("applyEartagBody");
            if (body) body.innerHTML = html;

            show(get("applyEartagPopup"));

            const selectAll = get("selectAllApplyTags");

            if (selectAll) {
                selectAll.onchange = function () {
                    document.querySelectorAll(".apply-checkbox")
                        .forEach(cb => cb.checked = selectAll.checked);
                };
            }

        })
        .catch(err => console.error("Apply eartags load error:", err));
}

function closeApplyEartagPopup() {
    hide(get("applyEartagPopup"));
}

function searchApplyEartagTable() {

    const input = get("applyTagSearch");
    const filter = input ? input.value.toUpperCase() : "";

    document.querySelectorAll("#applyEartagBody tr")
        .forEach(row => {

            const text = row.textContent.toUpperCase();
            row.style.display = text.includes(filter) ? "" : "none";

        });
}

/* =========================================================
   APPLY SELECTED EARTAGS
========================================================= */

function applySelectedEartags() {

    const selected = [];

    document.querySelectorAll(".apply-checkbox:checked")
        .forEach(cb => selected.push(cb.value));

    if (selected.length === 0) {
        alert("Select at least one eartag.");
        return;
    }

    showLoadingPopup();

    fetch("apply_eartags.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(selected)
    })
    .then(res => res.text())
    .then(msg => {

        hideLoadingPopup();

        alert(msg);

        // better than reload
        loadApplyEartags();

    })
    .catch(err => {

        hideLoadingPopup();

        console.error("Apply error:", err);

        alert("Failed to apply eartags");

    });
}

/* =========================================================
   BIRTH FORM HANDLER
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const birthForm = get("birthForm");

    if (!birthForm) {
        console.warn("birthForm not found");
        return;
    }

    birthForm.addEventListener("submit", function (e) {

        e.preventDefault();

        showLoadingPopup();

        const formData = new FormData(this);

        fetch("save_birth.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(data => {

            hideLoadingPopup();

            data = data.trim();

            console.log("Server:", data);

            if (data === "KRAAL_NOT_FOUND") {
                showErrorPopup("Kraal does not exist.");
                return;
            }

            if (data === "DAM_NOT_FOUND") {
                showErrorPopup("Dam does not exist in Master Register.");
                return;
            }

            if (data.startsWith("Success")) {
                closeBirthPopup();
                showSuccessPopup(data);
                return;
            }

            showErrorPopup(data);

        })
        .catch(err => {

            hideLoadingPopup();

            console.error(err);

            showErrorPopup("Error saving birth record");

        });

    });

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const permitInForm = document.querySelector('#permitInForm');

    if (!permitInForm) {
        console.error('permitInForm not found');
        return;
    }

    permitInForm.addEventListener('submit', function (e) {

        e.preventDefault();

        showLoadingPopup();

        const formData = new FormData(this);

        fetch("save_permit_in.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {

            hideLoadingPopup();

            data = data.trim();

            console.log("Server Response:", data);

            switch (data) {

                case "SUCCESS":
                    closePermitInPopup();
                    permitInForm.reset();
                    showSuccessPopup("Permit In was recorded successfully.");
                    loadPermitInRecords();
                    break;

                case "ANIMAL_EXISTS":
                    showWarningPopup("This animal is already registered in the Master Register.");
                    break;

                case "KRAAL_NOT_FOUND":
                    showWarningPopup("The selected kraal does not exist.");
                    break;

                case "DATABASE_ERROR":
                    showErrorPopup("A database error occurred while saving the record.");
                    break;

                default:
                    showErrorPopup("Unexpected response: " + data);
                    break;
            }

        })
        .catch(error => {

            hideLoadingPopup();

            console.error("Fetch error:", error);

            showErrorPopup("Unable to connect to the server. Please try again.");

        });

    });

});
</script>

<script>
/* =========================================================
   ERROR POPUP HANDLING
========================================================= */

function showErrorPopup(message) {

    const popup = document.getElementById('errorPoup');
    const body = document.querySelector('#errorPoup .popup-body');

    if (body) {
        body.innerHTML = `
            <div class="popup-icon error-icon">✖</div>
            <div>${message}</div>
        `;
    }

    if (popup) {
        popup.style.display = 'flex';
    }
}

function closeError() {
    const popup = document.getElementById('errorPoup');
    if (popup) popup.style.display = 'none';
}

/* =========================================================
   WARNING POPUP (FIXED MISSING FUNCTION)
========================================================= */

function showWarningPopup(message) {

    const popup = document.getElementById('errorPoup');
    const body = document.querySelector('#errorPoup .popup-body');

    if (body) {
        body.innerHTML = `
            <div class="popup-icon warning-icon">⚠</div>
            <div>${message}</div>
        `;
    }

    if (popup) {
        popup.style.display = 'flex';
    }
}

/* =========================================================
   PERMIT OUT POPUP
========================================================= */

function openPermitOutPopup() {
    const el = document.getElementById('permitOutPopup');
    if (el) el.style.display = 'flex';
}

function closePermitOutPopup() {
    const el = document.getElementById('permitOutPopup');
    if (el) el.style.display = 'none';
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function()
{
    const form = document.getElementById('permitOutForm');

    console.log("Permit form found:", form);

    if(!form) return;

    form.addEventListener('submit', function(e)
    {
        e.preventDefault();

        showLoadingPopup();

        fetch('save_permit_out.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(res => res.text())
        .then(data =>
        {
            hideLoadingPopup();

            data = data.trim();

            if(data === "ANIMAL_NOT_FOUND")
                return showErrorPopup("Animal not found");

            if(data === "KRAAL_NOT_FOUND")
                return showErrorPopup("Kraal not found");

            if(data.startsWith("Success"))
            {
                closePermitOutPopup();
                showSuccessPopup(data);
            }
        })
        .catch(err =>
        {
            hideLoadingPopup();
            showErrorPopup("System error");
            console.error(err);
        });
    });
});
</script>
<script>

// =============================
// TOGGLE DELETE MODE (DEATHS)
// =============================
function toggleDeathDeleteMode()
{
    const bar =
        document.getElementById("deathDeleteBar");

    const container =
        document.getElementById("tableContainer5");

    const selectCols =
        container.querySelectorAll(".select-col");

    if (bar.classList.contains("show"))
    {
        // Hide delete bar
        bar.classList.remove("show");

        // Remove delete mode
        container.classList.remove("delete-mode");

        // Hide Select column
        selectCols.forEach(function(col){
            col.style.display = "none";
        });

        // Uncheck Mark All
        const deathSelectAll =
    document.getElementById("deathSelectAll");

if(deathSelectAll){
    deathSelectAll.checked = false;
}

        // Uncheck all row checkboxes
        container.querySelectorAll(".death-row-check").forEach(function(box){
            box.checked = false;
        });
    }
    else
    {
        // Show delete bar
        bar.classList.add("show");

        // Enable delete mode
        container.classList.add("delete-mode");

        // Show Select column
        selectCols.forEach(function(col){
            col.style.display = "table-cell";
        });
    }
}

// =============================
// SELECT / DESELECT ALL DEATHS
// =============================
function toggleDeathSelectAll(source) {

    document.querySelectorAll(".death-row-check")
        .forEach(cb => cb.checked = source.checked);
}

</script>
<script>
function openDeathPopup()
{
    document.getElementById(
        'deathPopup'
    ).style.display = 'flex';
}

function closeDeathPopup()
{
    document.getElementById(
        'deathPopup'
    ).style.display = 'none';
}
</script>
<script>
document.addEventListener(
'DOMContentLoaded',
function()
{
    const deathForm =
        document.getElementById('deathForm');

    if(!deathForm) return;

    deathForm.addEventListener(
    'submit',
    function(e)
    {
        e.preventDefault();

        showLoadingPopup();

        fetch(
            'save_death.php',
            {
                method:'POST',
                body:new FormData(this)
            }
        )
        .then(res => res.text())
        .then(data =>
        {
            hideLoadingPopup();

            data = data.trim();

            console.log(
                "Server response:",
                data
            );

            if(data ===
                'ANIMAL_NOT_FOUND')
            {
                showErrorPopup(
                    'Animal does not exist in Master Register'
                );
                return;
            }

            if(data ===
                'KRAAL_NOT_FOUND')
            {
                showErrorPopup(
                    'Kraal does not exist'
                );
                return;
            }

            if(data.startsWith(
                'Success'))
            {
                closeDeathPopup();

                showSuccessPopup(
                    data
                );

                return;
            }

            showErrorPopup(data);
        })
        .catch(error =>
        {
            hideLoadingPopup();

            console.error(error);

            showErrorPopup(
                'System error occurred'
            );
        });

    });
});
</script>
<script>
function togglePermitInDeleteMode()
{
    const bar =
        document.getElementById("permitInDeleteBar");

    const container =
        document.getElementById("tableContainer3");

    const selectCols =
        container.querySelectorAll(".select-col");

    if(bar.classList.contains("show"))
    {
        bar.classList.remove("show");

        container.classList.remove("delete-mode");

        selectCols.forEach(function(col){

            col.style.display = "none";

        });

        document.getElementById(
            "permitInSelectAll"
        ).checked = false;

        container.querySelectorAll(
            ".permitin-row-check"
        ).forEach(function(box){

            box.checked = false;

        });
    }
    else
    {
        bar.classList.add("show");

        container.classList.add("delete-mode");

        selectCols.forEach(function(col){

            col.style.display = "table-cell";

        });
    }
}
function togglePermitInSelectAll(source)
{
    document.querySelectorAll(
        ".permitin-row-check"
    ).forEach(function(box){

        box.checked = source.checked;

    });
}</script>
<script>

function hidePermitInRecords() {

    const selectedIds = [];

    document.querySelectorAll(".permitin-row-check:checked")
        .forEach(cb => {
            selectedIds.push(cb.dataset.id);
        });

    console.log("Selected IDs:", selectedIds);

    fetch("hide_permitin_records.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            ids: selectedIds
        })
    })

    .then(response => response.text())
    .then(text => {

        console.log("Server response:", text);

        const data = JSON.parse(text);

        document.getElementById("loadingPoup").style.display = "none";

        if (data.success) {

            // Hide rows instantly in UI
            selectedIds.forEach(id => {
                const checkbox = document.querySelector(`.permitin-row-check[data-id="${id}"]`);

                if (checkbox) {
                    const row = checkbox.closest("tr");
                    if (row) {
                        row.style.display = "none";
                    }
                }
            });

            openHideSuccessPopup();

        } else {
            alert(data.message || "Failed to hide records");
        }
    })

    .catch(error => {

        document.getElementById("loadingPoup").style.display = "none";

        console.error("Error:", error);

    });

}


function openPermitInHideConfirmPopup() {

    const selectedIds = document.querySelectorAll(".permitin-row-check:checked");

    if (selectedIds.length === 0) {
        openNoSelectionPopup();
        return;
    }

    document.getElementById("permitInHideConfirmPopup").style.display = "flex";
}


function closePermitInHideConfirmPopup() {
    document.getElementById("permitInHideConfirmPopup").style.display = "none";
}


function confirmPermitInHideSelected() {

    const activeTab = document.querySelector(".tab-content.active");

    if (activeTab) {
        sessionStorage.setItem("activeHideTab", activeTab.id);
    }

    closePermitInHideConfirmPopup();

    document.getElementById("loadingPoup").style.display = "flex";

    hidePermitInRecords();
}

</script>
<script>

/*=========================================================
    LOAD PERMIT IN RECORDS
=========================================================*/

function loadPermitInRecords()
{
    showLoad();

    fetch("fetch_permits_in.php")

    .then(response => response.text())

    .then(data => {

        document.getElementById(
            "permitInRecords"
        ).innerHTML = data;

        hideLoad();

    })

    .catch(function(error){

        hideLoad();

        console.error(error);

        document.getElementById(
            "permitInRecords"
        ).innerHTML = `
            <tr>
                <td colspan="9" class="no-data">
                    Failed to load Permit In records.
                </td>
            </tr>
        `;

    });

}

/*=========================================================
    GENERIC TABLE REFRESH
=========================================================*/

function refreshTable(url, containerId){

    fetch(url)

    .then(response => response.text())

    .then(html => {

        const container =
            document.getElementById(containerId);

        if(container){

            container.innerHTML = html;

        }

    })

    .catch(error => {

        console.error(
            "Failed to load " + url,
            error
        );

    });

}


/*=========================================================
    REFRESH ALL TABLES
=========================================================*/

function refreshAllTables(){

    refreshTable(
        "fetch_births.php",
        "birthRecords"
    );

    refreshTable(
        "fetch_deaths.php",
        "deathRecords"
    );

    refreshTable(
        "fetch_master_register.php",
        "masterRecords"
    );

    refreshTable(
        "fetch_permits_in.php",
        "permitInRecords"
    );

    refreshTable(
        "fetch_permits_out.php",
        "permitOutRecords"
    );

    refreshTable(
        "fetch_fate_register.php",
        "fateRecords"
    );

}


/*=========================================================
    SHOW / HIDE SELECT COLUMN
=========================================================*/

function toggleSelectColumn(tabId, show){

    const tab =
        document.getElementById(tabId);

    if(!tab){

        console.error(
            "Tab not found:",
            tabId
        );

        return;

    }

    tab.querySelectorAll(".select-col")

    .forEach(function(cell){

        cell.style.display =
            show ? "table-cell" : "none";

    });

}


/*=========================================================
    LOADING POPUP
=========================================================*/

function showLoadingPopup(){

    document.getElementById(
        "loadingPopup"
    ).style.display = "flex";

}

function hideLoadingPopup(){

    document.getElementById(
        "loadingPopup"
    ).style.display = "none";

}


/*=========================================================
    SUCCESS POPUP
=========================================================*/

function showSuccessPopup(message){

    document.getElementById(
        "successPopupMessage"
    ).textContent = message;

    document.getElementById(
        "successPopup"
    ).style.display = "flex";

}

function closeSuccessPopup(){

    document.getElementById(
        "successPopup"
    ).style.display = "none";

}


/*=========================================================
    WARNING POPUP
=========================================================*/

function showWarningPopup(message){

    document.getElementById(
        "warningPopupMessage"
    ).textContent = message;

    document.getElementById(
        "warningPopup"
    ).style.display = "flex";

}

function closeWarningPopup(){

    document.getElementById(
        "warningPopup"
    ).style.display = "none";

}


/*=========================================================
    ERROR POPUP
=========================================================*/

function showErrorPopup(message){

    document.getElementById(
        "errorPopupMessage"
    ).textContent = message;

    document.getElementById(
        "errorPopup"
    ).style.display = "flex";

}

function closeErrorPopup(){

    document.getElementById(
        "errorPopup"
    ).style.display = "none";

}


/*=========================================================
    UPDATE USER PRESENCE
=========================================================*/

function updatePresence(){

    fetch("update_status.php",{

        method:"POST",

        credentials:"include"

    })

    .catch(error=>{

        console.error(
            "Presence update failed.",
            error
        );

    });

}


/*=========================================================
    START PRESENCE SERVICE
=========================================================*/

updatePresence();

setInterval(

    updatePresence,

    20000

);

setInterval(function(){

    fetch("presence_cleanup.php")

    .catch(error=>{

        console.error(error);

    });

},60000);

</script>
<script>

/*=========================================================
    TOGGLE PERMIT OUT DELETE MODE
=========================================================*/

function togglePermitOutDeleteMode(){

    const bar =
        document.getElementById("permitOutDeleteBar");

    const container =
        document.getElementById("tableContainer4");

    if(!bar || !container){

        console.error("Permit Out elements not found");
        return;

    }

    const selectCols =
        container.querySelectorAll(".select-col");

    if(bar.classList.contains("show")){

        /* Hide delete bar */
        bar.classList.remove("show");

        /* Remove delete mode */
        container.classList.remove("delete-mode");

        /* Hide select column */
        selectCols.forEach(function(col){
            col.style.display = "none";
        });

        /* Uncheck select all */
        const selectAll =
            document.getElementById("permitOutSelectAll");

        if(selectAll) selectAll.checked = false;

        /* Uncheck all rows */
        container.querySelectorAll(".permitout-row-check")
            .forEach(function(cb){
                cb.checked = false;
            });

    }else{

        /* Show delete bar */
        bar.classList.add("show");

        container.classList.add("delete-mode");

        selectCols.forEach(function(col){
            col.style.display = "table-cell";
        });

    }

}


/*=========================================================
    TOGGLE SELECT ALL (PERMIT OUT)
=========================================================*/

function togglePermitOutSelectAll(source){

    document.querySelectorAll(".permitout-row-check")
        .forEach(function(cb){
            cb.checked = source.checked;
        });

}


/*=========================================================
    OPEN CONFIRM DELETE POPUP
=========================================================*/

function openPermitOutHideConfirmPopup(){

    const selected =
        document.querySelectorAll(".permitout-row-check:checked");

    if(selected.length === 0){

        showWarningPopup(
            "Please select at least one Permit Out record."
        );

        return;

    }

    document.getElementById(
        "permitOutHideConfirmPopup"
    ).style.display = "flex";

}


/*=========================================================
    CLOSE CONFIRM POPUP
=========================================================*/

function closePermitOutHideConfirmPopup(){

    document.getElementById(
        "permitOutHideConfirmPopup"
    ).style.display = "none";

}


/*=========================================================
    HIDE PERMIT OUT RECORDS
=========================================================*/

function hidePermitOutRecords(){

    const selectedIds = [];

    document.querySelectorAll(".permitout-row-check:checked")
        .forEach(cb => {
            selectedIds.push(cb.dataset.id);
        });

    if(selectedIds.length === 0){

        showWarningPopup(
            "No records selected."
        );

        return;

    }

    showLoadingPopup();

    fetch("hide_permitout_records.php",{

        method:"POST",

        headers:{
            "Content-Type":"application/json"
        },

        body: JSON.stringify({
            ids: selectedIds
        })

    })

    .then(response => response.text())

    .then(text => {

        hideLoadingPopup();

        console.log("Server response:", text);

        let data;

        try{

            data = JSON.parse(text);

        }catch(e){

            showErrorPopup(
                "Invalid server response."
            );

            return;

        }

        if(data.success){

            showSuccessPopup(
                "Permit Out records hidden successfully."
            );

            loadPermitOutRecords();

        }else{

            showErrorPopup(
                data.message || "Failed to hide records."
            );

        }

    })

    .catch(error => {

        hideLoadingPopup();

        console.error(error);

        showErrorPopup(
            "System error occurred while hiding records."
        );

    });

}


/*=========================================================
    LOAD PERMIT OUT RECORDS
=========================================================*/

function loadPermitOutRecords(){

    refreshTable(
        "fetch_permits_out.php",
        "permitOutRecords"
    );

}

</script>
<script>

/*=========================================================
    TOGGLE DEATH SELECT ALL
=========================================================*/

function toggleDeathSelectAll(source){

    document.querySelectorAll(".death-row-check")
        .forEach(cb => cb.checked = source.checked);

}


/*=========================================================
    OPEN DEATH CONFIRM POPUP
=========================================================*/

function openDeathHideConfirmPopup(){

    const selected =
        document.querySelectorAll(".death-row-check:checked");

    if(selected.length === 0){

        showWarningPopup(
            "Please select at least one Death record."
        );

        return;

    }

    document.getElementById(
        "deathHideConfirmPopup"
    ).style.display = "flex";

}


/*=========================================================
    CLOSE DEATH CONFIRM POPUP
=========================================================*/

function closeDeathHideConfirmPopup(){

    document.getElementById(
        "deathHideConfirmPopup"
    ).style.display = "none";

}


/*=========================================================
    HIDE DEATH RECORDS
=========================================================*/

function hideDeathRecords(){

    const selectedIds = [];

    document.querySelectorAll(".death-row-check:checked")
        .forEach(cb => {
            selectedIds.push(cb.dataset.id);
        });

    if(selectedIds.length === 0){

        showWarningPopup(
            "No Death records selected."
        );

        return;

    }

    showLoadingPopup();

    fetch("hide_death_records.php",{

        method:"POST",

        headers:{
            "Content-Type":"application/json"
        },

        body: JSON.stringify({
            ids: selectedIds
        })

    })

    .then(response => response.text())

    .then(text => {

        hideLoadingPopup();

        console.log("Server response:", text);

        let data;

        try{

            data = JSON.parse(text);

        }catch(e){

            showErrorPopup(
                "Invalid server response."
            );

            return;

        }

        if(data.success){

            /* Remove rows instantly */
            selectedIds.forEach(id => {

                const row =
                    document.querySelector(`tr[data-id="${id}"]`);

                if(row){
                    row.remove();
                }

            });

            showSuccessPopup(
                "Death records hidden successfully."
            );

            refreshTable(
                "fetch_deaths.php",
                "deathRecords"
            );

        }else{

            showErrorPopup(
                data.message || "Failed to hide death records."
            );

        }

    })

    .catch(error => {

        hideLoadingPopup();

        console.error(error);

        showErrorPopup(
            "System error occurred while processing request."
        );

    });

}


/*=========================================================
    REFRESH DEATH RECORDS (MANUAL)
=========================================================*/

function refreshDeathRecords(){

    refreshTable(
        "fetch_deaths.php",
        "deathRecords"
    );

}


/*=========================================================
    SETTINGS MENU TOGGLE (DEATH)
=========================================================*/

function toggleDeathSettingsMenu(event){

    event.stopPropagation();

    const menu =
        document.getElementById("deathSettingsMenu");

    if(!menu){
        console.error("Death settings menu not found");
        return;
    }

    menu.style.display =
        (menu.style.display === "block")
        ? "none"
        : "block";

}


/*=========================================================
    CLOSE SETTINGS MENU ON OUTSIDE CLICK
=========================================================*/

document.addEventListener("click", function(e){

    const menu =
        document.getElementById("deathSettingsMenu");

    const button =
        document.getElementById("stng-cattle");

    if(menu && button){

        if(!menu.contains(e.target) && !button.contains(e.target)){

            menu.style.display = "none";

        }

    }

});


/*=========================================================
    FILTER MENU (PLACEHOLDER)
=========================================================*/

function openDeathFilterMenu(){

    showWarningPopup(
        "Filter menu will be implemented soon."
    );

}

</script>
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
<script>

const birthForm = document.getElementById("birthForm");

if(birthForm){

    birthForm.addEventListener("submit", function(e){

        // STOP PAGE RELOAD
        e.preventDefault();

        // SHOW LOADING
        document.getElementById("loadingPopup").style.display = "flex";

        // FORM DATA
        let formData = new FormData(birthForm);

        // SEND TO PHP
        fetch("contact4.php", {

            method: "POST",
            body: formData

        })

        .then(response => response.text())

        .then(data => {

            // HIDE LOADING
            document.getElementById("loadingPopup").style.display = "none";

            // SUCCESS
            if(data.trim() === "success"){

                // SHOW SUCCESS POPUP
                document.getElementById("successPopup").style.display = "flex";

                // RESET FORM
                birthForm.reset();

refreshAllRegisters();

                // KEEP BIRTH TAB ACTIVE
                document.querySelectorAll(".tab-content").forEach(tab=>{

                    tab.style.display = "none";
                    tab.classList.remove("active");

                });

                document.querySelectorAll(".tab-btn").forEach(btn=>{

                    btn.classList.remove("active");

                });

                document.getElementById("births").style.display = "block";
                document.getElementById("births").classList.add("active");

                document.querySelector('[data-tab="births"]').classList.add("active");

                // RELOAD RECORDS TABLE
                loadBirthRecords();

            }

            // ERROR
            else{

                document.getElementById("errorPopup").style.display = "flex";

            }

        })

        .catch(error => {

            document.getElementById("loadingPopup").style.display = "none";

            document.getElementById("errorPopup").style.display = "flex";

        });

    });

}

/* LOAD UPDATED RECORDS */

function loadBirthRecords(){

    fetch("fetch_births.php")

    .then(response => response.text())

    .then(data => {

        document.getElementById("birthRecords").innerHTML = data;

    });

}

function loadPermitOutRecords(){

    fetch("fetch_permits_out.php")

    .then(response => response.text())

    .then(data => {

        document.getElementById("permitOutRecords").innerHTML = data;

    });

}

/* CLOSE POPUPS */

function closeSuccessPopup(){

    document.getElementById("successPopup").style.display = "none";

}

function closeErrorPopup(){

    document.getElementById("errorPopup").style.display = "none";

}

function closeExistsPopup(){

    document.getElementById("existsPopup").style.display = "none";

}

const urlParams = new URLSearchParams(window.location.search);

const status = urlParams.get("status");

const activeTab = urlParams.get("tab");

/* OPEN SPECIFIC TAB */

if(activeTab){

    document.querySelectorAll(".tab-content").forEach(tab => {

        tab.style.display = "none";
        tab.classList.remove("active");

    });

    document.querySelectorAll(".tab-btn").forEach(btn => {

        btn.classList.remove("active");

    });

    const selectedTab = document.getElementById(activeTab);

    if(selectedTab){

        selectedTab.style.display = "block";
        selectedTab.classList.add("active");

    }

    const selectedButton =
    document.querySelector(`[data-tab="${activeTab}"]`);

    if(selectedButton){

        selectedButton.classList.add("active");

    }

}

/* SUCCESS POPUP */

if(status === "success"){

    document.getElementById("successPopup").style.display = "flex";

}

/* DUPLICATE POPUP */

if(status === "exists"){

    document.getElementById("existsPopup").style.display = "flex";

}

</script>
<script>

const permitOutForm = document.getElementById("permitOutForm");
console.log("Permit form found:", permitOutForm);

if(permitOutForm){

    permitOutForm.addEventListener("submit", function(e){

        // STOP NORMAL SUBMIT
        e.preventDefault();

        // SHOW LOADING
        document.getElementById("loadingPopup").style.display = "flex";

        // FORM DATA
        let formData = new FormData(permitOutForm);

        // SEND TO PHP
        fetch("contact7.php", {

            method: "POST",
            body: formData

        })

        .then(response => response.text())

        .then(data => {

            // HIDE LOADING
            document.getElementById("loadingPopup").style.display = "none";

            data = data.trim();

            // SUCCESS
if(data === "success"){

    document.getElementById("successPopup")
        .style.display = "flex";

    permitOutForm.reset();

    loadPermitOutRecords();
	
refreshAllRegisters();

}

            // DUPLICATE
            else if(data === "duplicate"){

                document.getElementById("duplicatePopup")
                    .style.display = "flex";

            }

            // NOT FOUND
            else if(data === "notfound"){

                document.getElementById("notFoundPopup")
                    .style.display = "flex";

            }

            // ERROR
            else{

                document.getElementById("errorPopup")
                    .style.display = "flex";

            }

            // KEEP PERMIT OUT TAB ACTIVE
            document.querySelectorAll(".tab-content").forEach(tab => {

                tab.style.display = "none";
                tab.classList.remove("active");

            });

            document.querySelectorAll(".tab-btn").forEach(btn => {

                btn.classList.remove("active");

            });

            document.getElementById("permitsout").style.display = "block";
            document.getElementById("permitsout").classList.add("active");

            document.querySelector('[data-tab="permitsout"]')
                .classList.add("active");

        })

        .catch(error => {

            document.getElementById("loadingPopup").style.display = "none";

            document.getElementById("errorPopup")
                .style.display = "flex";

        });

    });

}

/* CLOSE POPUPS */

function closeNotFoundPopup(){

    document.getElementById("notFoundPopup").style.display = "none";

}

function closeDuplicatePopup(){

    document.getElementById("duplicatePopup").style.display = "none";

}

</script>
<script>

let currentAnimal = {};

/* =========================
   OPEN ANIMAL PROFILE
========================= */

function openAnimalPopup(
    id,
    animal_id,
    birth_date,
    sex,
    breed,
    color,
    kraal,
    diptank,
    registration,
    source,
    alterations,
    Tenure
){

    /* STORE CURRENT ANIMAL */

    currentAnimal = {
        id,
        animal_id,
        birth_date,
        sex,
        breed,
        color,
        kraal,
        diptank,
	Tenure,
        registration,
        source,
        alterations
    };

    /* POPULATE PROFILE POPUP */

    document.getElementById("profileAnimalID").innerText = animal_id;
    document.getElementById("profileBirthDate").innerText = birth_date;
    document.getElementById("profileSex").innerText = sex;
    document.getElementById("profileBreed").innerText = breed;
    document.getElementById("profileColor").innerText = color;
    document.getElementById("profileKraal").innerText = kraal;
    document.getElementById("profileDiptank").innerText = diptank;
    document.getElementById("profileTenure").innerText = Tenure;
    document.getElementById("profileSource").innerText = source;
    document.getElementById("profileAlterations").innerText = alterations;

    document.getElementById("animalProfilePopup").style.display = "flex";
}

/* =========================
   CLOSE PROFILE POPUP
========================= */

function closeAnimalPopup(){

    document.getElementById("animalProfilePopup").style.display = "none";

}

/* =========================
   EDIT BUTTON
========================= */

document.addEventListener("click", function(e){

    if(e.target.id === "editAnimalBtn"){

        document.getElementById("editID").value =
            currentAnimal.id;

        document.getElementById("editAnimalID").value =
            currentAnimal.animal_id;

        document.getElementById("editBirthDate").value =
            currentAnimal.birth_date;

        document.getElementById("editSex").value =
            currentAnimal.sex;

        document.getElementById("editBreed").value =
            currentAnimal.breed;

        document.getElementById("editColor").value =
            currentAnimal.color;

        document.getElementById("editKraal").value =
            currentAnimal.kraal;

        document.getElementById("editDiptank").value =
            currentAnimal.diptank;

        document.getElementById("editTenure").value =
            currentAnimal.Tenure;

        document.getElementById("editSource").value =
            currentAnimal.source;

        document.getElementById("editAlterations").value =
            currentAnimal.alterations;

        document.getElementById("editAnimalPopup")
            .style.display = "flex";
    }

});

/* =========================
   CLOSE EDIT POPUP
========================= */

function closeEditAnimalPopup(){

    document.getElementById("editAnimalPopup")
        .style.display = "none";

}

/* =========================
   MEDICAL HISTORY BUTTON
========================= */

const medicalHistoryBtn =
    document.getElementById("medicalHistoryBtn");

if(medicalHistoryBtn){

    medicalHistoryBtn.addEventListener("click", function(){

        window.location.href =
            "medical_history.php?animal_id="
            + currentAnimal.animal_id;

    });

}

window.addEventListener("load", function(){

    const params =
        new URLSearchParams(window.location.search);

    if(params.get("tab") === "master"){

        document.querySelectorAll(".tab-content")
        .forEach(tab => {

            tab.style.display = "none";
            tab.classList.remove("active");

        });

        document.querySelectorAll(".tab-btn")
        .forEach(btn => {

            btn.classList.remove("active");

        });

        document.getElementById("master")
            .style.display = "block";

        document.getElementById("master")
            .classList.add("active");

        document.querySelector('[data-tab="master"]')
            .classList.add("active");

    }

});

</script>
<script>

const tabs = document.querySelectorAll(".tab-btn");

tabs.forEach(tab => {

    tab.addEventListener("click", () => {

        const target = tab.getAttribute("data-tab");

        // REMOVE ACTIVE
        document.querySelectorAll(".tab-btn").forEach(btn => {
            btn.classList.remove("active");
        });

        document.querySelectorAll(".tab-content").forEach(content => {
            content.classList.remove("active");
            content.style.display = "none";
        });

        // ACTIVATE BUTTON
        tab.classList.add("active");

        // ACTIVATE TARGET TAB
        const activeTab = document.getElementById(target);

        if(activeTab){

            activeTab.style.display = "block";
            activeTab.classList.add("active");

        }

    });

});

</script>

<script>


    // SUCCESS POPUP
    function showSuccessPopup(){

        document.getElementById("loadingPopup").style.display = "none";

        document.getElementById("successPopup").style.display = "flex";
 // Refresh all records
    refreshAllTables();
    }

    // ERROR POPUP
    function showErrorPopup(){

        document.getElementById("loadingPopup").style.display = "none";

        document.getElementById("errorPopup").style.display = "flex";
    }

    // CLOSE SUCCESS
    function closeSuccessPopup(){

        document.getElementById("successPopup").style.display = "none";

    }

    // CLOSE ERROR
    function closeErrorPopup(){

        document.getElementById("errorPopup").style.display = "none";

    }

</script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    const deathForm = document.getElementById("deathForm");

    if(deathForm){

        deathForm.addEventListener("submit", function(e){

            e.preventDefault();

            document.getElementById("loadingPopup").style.display = "flex";

            let formData = new FormData(this);

            fetch("contact_death.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {

                document.getElementById("loadingPopup").style.display = "none";

if(data.trim() === "success"){

    document.getElementById("successPopup").style.display = "flex";

    deathForm.reset();

refreshAllRegisters();

    loadDeathRecords();
                    // KEEP DEATH TAB OPEN
                    document.querySelectorAll(".tab-content").forEach(tab=>{
                        tab.style.display = "none";
                        tab.classList.remove("active");
                    });

                    document.querySelectorAll(".tab-btn").forEach(btn=>{
                        btn.classList.remove("active");
                    });

                    document.getElementById("deaths").style.display = "block";
                    document.getElementById("deaths").classList.add("active");

                    document.querySelector('[data-tab="deaths"]')
                        .classList.add("active");

                }else{

                    document.getElementById("errorPopup").style.display = "flex";

                }

            })
            .catch(error => {

                document.getElementById("loadingPopup").style.display = "none";
                document.getElementById("errorPopup").style.display = "flex";

            });

        });
     function loadDeathRecords(){

    fetch("fetch_deaths.php")
    .then(response => response.text())
    .then(data => {

        document.getElementById("deathRecords").innerHTML = data;

    });

}
    }

});
</script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    const editForm = document.getElementById("editAnimalForm");

    console.log("Form found:", editForm);

    if(editForm){

        editForm.addEventListener("submit", function(e){

            e.preventDefault();

            console.log("Submit intercepted");

            let formData = new FormData(this);
		console.log("Updating row ID:", document.getElementById("editID").value);

            fetch("update_animal.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {

                console.log("Server response:", data);

                if(data.trim() === "success"){

                    closeEditAnimalPopup();
                    closeAnimalPopup();

                    loadMasterRecords();
			loadFateRecords();

document.getElementById("successPopup")
    .style.display = "flex";

                }else{

                    alert("Server returned: " + data);

                }

            })
            .catch(error => {

                console.log("Fetch error:", error);

                alert("Update failed");

            });

        });

    }else{

        console.log("editAnimalForm NOT FOUND");

    }

});
</script>
<script>
const permitInForm = document.getElementById("permitInForm");

if(permitInForm){

    permitInForm.addEventListener("submit", function(e){

        e.preventDefault();

        // SHOW LOADING POPUP
        document.getElementById("loadingPopup").style.display = "flex";

        let formData = new FormData(this);

        fetch("contact6.php", {
            method: "POST",
            body: formData
        })

        .then(response => response.text())

        .then(data => {

            // HIDE LOADING POPUP
            document.getElementById("loadingPopup").style.display = "none";

            data = data.trim();

            if(data === "success"){

                document.getElementById("successPopup")
                    .style.display = "flex";

		refreshAllRegisters();

                permitInForm.reset();

                // KEEP PERMITS IN TAB ACTIVE
                document.querySelectorAll(".tab-content").forEach(tab => {
                    tab.style.display = "none";
                    tab.classList.remove("active");
                });

                document.querySelectorAll(".tab-btn").forEach(btn => {
                    btn.classList.remove("active");
                });

                document.getElementById("permitsin").style.display = "block";
                document.getElementById("permitsin").classList.add("active");

                document.querySelector('[data-tab="permitsin"]')
                    .classList.add("active");

            }else{

                document.getElementById("errorPopup")
                    .style.display = "flex";

            }

        })

        .catch(error => {

            document.getElementById("loadingPopup").style.display = "none";

            document.getElementById("errorPopup")
                .style.display = "flex";

        });

    });

}
function loadMasterRecords(){

    fetch("fetch_master_register.php")

    .then(response => response.text())

    .then(data => {

        document.getElementById("masterRecords").innerHTML = data;

    });

}

function loadFateRecords(){

    fetch("fetch_fate_register.php")

    .then(response => response.text())

    .then(data => {

        document.getElementById("fateRecords").innerHTML = data;

    });

}
function refreshAllRegisters(){

    loadMasterRecords();
    loadFateRecords();

}
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

</script>
<script>

function toggleMessagesPanel(){

    const panel = document.getElementById("messagesPanel");

    console.log("messagesPanel =", panel);

    if(!panel){
        console.error("messagesPanel not found");
        return;
    }

    panel.classList.toggle("active");

    savePanelState();
}
</script>

<script>
function openNewMessagePanel()
{
    const panel =
        document.getElementById("newMessagePanel");

    if(!panel)
    {
        console.error(
            "newMessagePanel element not found"
        );
        return;
    }

    panel.classList.add("active");

    if(typeof savePanelState === "function")
    {
        savePanelState();
    }
}

function closeNewMessage()
{
    const panel =
        document.getElementById("newMessagePanel");

    if(panel)
    {
        panel.classList.remove("active");
    }

    const chatPanel =
        document.getElementById("chatPanel");

    if(
        chatPanel &&
        chatPanel.classList.contains("active")
    )
    {
        chatPanel.style.right = "380px";
    }

    if(typeof savePanelState === "function")
    {
        savePanelState();
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

        chatBody.innerHTML = data;

        chatBody.scrollTop =
            chatBody.scrollHeight;
    });

    // Mark messages from this user as read
    fetch("mark_messages_read.php?user_id=" + userId);
}
function openConversation(userId, userName)
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

window.addEventListener("DOMContentLoaded", function () {

    const messagesPanel =
        document.getElementById("messagesPanel");

    const newMessagePanel =
        document.getElementById("newMessagePanel");

    const chatPanel =
        document.getElementById("chatPanel");

    if (messagesPanel && localStorage.getItem("messagesPanelOpen") === "true") {
        messagesPanel.classList.add("active");
    }

    if (newMessagePanel && localStorage.getItem("newMessagePanelOpen") === "true") {
        newMessagePanel.classList.add("active");
    }

    if (chatPanel && localStorage.getItem("chatPanelOpen") === "true") {

        chatPanel.classList.add("active");

        const userId = parseInt(localStorage.getItem("activeUser") || 0);
        const userName = localStorage.getItem("chatUserName") || "";

        if (userId > 0) {
            activeUser = userId;

            const nameEl = document.getElementById("chatUserName");
            if (nameEl) nameEl.innerText = userName;

            loadMessages(userId);
        }
    }

});
</script>
<script>
function viewNote(title, content, date)
{
    document.getElementById("viewNoteTitle").innerText = title;

    document.getElementById("viewNoteContent").innerText =
        content;

    document.getElementById("viewNoteDate").innerText =
        "Created: " + date;

    document.getElementById("viewNotePopup").style.display =
        "flex";
}

function closeNoteViewer()
{
    document.getElementById("viewNotePopup").style.display =
        "none";
}
</script>
<script>
function savePanelState()
{
    console.log("Saving panel state...");

    localStorage.setItem(
        "messagesPanelOpen",
        document.getElementById("messagesPanel")
            ?.classList.contains("active") || false
    );

    localStorage.setItem(
        "newMessagePanelOpen",
        document.getElementById("newMessagePanel")
            ?.classList.contains("active") || false
    );

    localStorage.setItem(
        "chatPanelOpen",
        document.getElementById("chatPanel")
            ?.classList.contains("active") || false
    );

    localStorage.setItem("activeUser", activeUser || 0);

    console.log("Saved:", {
        messagesPanelOpen: localStorage.getItem("messagesPanelOpen"),
        newMessagePanelOpen: localStorage.getItem("newMessagePanelOpen"),
        chatPanelOpen: localStorage.getItem("chatPanelOpen"),
        activeUser: localStorage.getItem("activeUser")
    });
}
</script>
<script>
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

        // Mark announcements as read
        fetch("mark_announcements_read.php")
        .then(response => response.text())
        .then(data => {

            // Hide badge after opening announcements
            const badge =
                document.getElementById("announcementBadge");

            if(badge)
            {
                badge.style.display = "none";
                badge.innerHTML = "0";
            }

        });
    }
}
</script>
<script>
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
</script>
<script>

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
</script>
<script>
function updateUnreadMessages() {
    fetch("get_unread_count.php")
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
</script>
<script>

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

</script>
<script>
function showLoad() {
    document.getElementById("loadingPoup").style.display = "flex";
}

function closeError() {
    document.getElementById("errorPoup").style.display = "none";
}

window.addEventListener("load", function () {

    const loading = document.getElementById("loadingPoup");
    const error = document.getElementById("errorPoup");

    if (loading) loading.style.display = "none";

    <?php if (isset($_POST['save']) && $kraal_exists === false) { ?>
        if (error) error.style.display = "flex";
    <?php } ?>

});
</script>
<script>
document.addEventListener("input", function (e) {
    if (e.target.classList.contains("numeric-only")) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
    }
});
</script>
<script>
function toggleTableView() {
    const table = document.getElementById("tableContainer");
    table.classList.toggle("expanded");
}
</script>
<script>
function toggleTableView() {
    document.getElementById("tableContainer")
            .classList.toggle("expanded");
}

function closeTableView() {
    document.getElementById("tableContainer")
            .classList.remove("expanded");
}
</script>
<script>
function toggleTableViewBirths() {
    document.getElementById("tableContainer2")
            .classList.toggle("expanded");
}

function closeTableViewBirths() {
    document.getElementById("tableContainer2")
            .classList.remove("expanded");
}
</script>
<script>
function toggleTableViewPermitIn() {
    document.getElementById("tableContainer3")
            .classList.toggle("expanded");
}

function closeTableViewPermitIn() {
    document.getElementById("tableContainer3")
            .classList.remove("expanded");
}
</script>
<script>
function toggleTableViewPermitOut() {
    document.getElementById("tableContainer4")
            .classList.toggle("expanded");
}

function closeTableViewPermitOut() {
    document.getElementById("tableContainer4")
            .classList.remove("expanded");
}
</script>
<script>
function toggleTableViewDeath() {
    document.getElementById("tableContainer5")
            .classList.toggle("expanded");
}

function closeTableViewDeath() {
    document.getElementById("tableContainer5")
            .classList.remove("expanded");
}
</script>
<script>
function toggleTableViewMaster() {
    document.getElementById("tableContainer6")
            .classList.toggle("expanded");
}

function closeTableViewMaster() {
    document.getElementById("tableContainer6")
            .classList.remove("expanded");
}
</script>
<script>
function toggleTableViewFate() {
    document.getElementById("tableContainer7")
            .classList.toggle("expanded");
}

function closeTableViewFate() {
    document.getElementById("tableContainer7")
            .classList.remove("expanded");
}
</script>
<script>
function openTransferPopup() {
    document.getElementById("transferPopup").style.display = "flex";
}
</script>
<script>
function showLoad()
{
    document.getElementById(
        "loadingPoup"
    ).style.display = "flex";
}

function hideLoad()
{
    document.getElementById(
        "loadingPoup"
    ).style.display = "none";
}
function closeErrorPopup()
{
    document.getElementById(
        "errorPopup"
    ).style.display = "none";
}

function retrieveRecord()
{
    showLoad();

    let startTime = Date.now();

    let form =
        document.getElementById(
            "dailyRecordForm"
        );

    let formData =
        new FormData(form);

    fetch("retrieve_record.php", {

        method: "POST",
        body: formData

    })

    .then(response => response.json())

    .then(data => {

        document.querySelector(
            'input[name="prev"]'
        ).value = data.om;

        document.querySelector(
            'input[name="on_register"]'
        ).value = data.or;

        document.querySelector(
            'input[name="dipped"]'
        ).value = data.dip;

        let elapsed =
            Date.now() - startTime;

        let remaining =
            Math.max(0, 2000 - elapsed);

        setTimeout(function(){

            hideLoad();

        }, remaining);

    })

   .catch(error => {

    console.error(error);

    let elapsed =
        Date.now() - startTime;

    let remaining =
        Math.max(0, 2000 - elapsed);

    setTimeout(function(){

        hideLoad();

        document.getElementById(
            "errorPopup"
        ).style.display = "flex";

    }, remaining);

});
}
</script>
<script>
function openRecordPopup()
{
    document.getElementById(
        "recordPopup"
    ).style.display = "flex";
}

function closeRecordPopup()
{
    document.getElementById(
        "recordPopup"
    ).style.display = "none";
}
</script>
<script>
function showLoad()
{
    document.getElementById(
        "loadingPoup"
    ).style.display = "flex";
}

function hideLoad()
{
    document.getElementById(
        "loadingPoup"
    ).style.display = "none";
}
function closeErrorPopup()
{
    document.getElementById(
        "errorPopup"
    ).style.display = "none";
}

function retrieveRecord()
{
    showLoad();

    let startTime = Date.now();

    let form =
        document.getElementById(
            "dailyRecordForm"
        );

    let formData =
        new FormData(form);

    fetch("retrieve_record.php", {

        method: "POST",
        body: formData

    })

    .then(response => response.json())

   .then(data => {

    console.log(data);

    let prevField =
        document.querySelector(
            'input[name="prev"]'
        );

    let registerField =
        document.querySelector(
            'input[name="on_register"]'
        );

    let dippedField =
        document.querySelector(
            'input[name="dipped"]'
        );

    console.log("prev:", prevField);
    console.log("on_register:", registerField);
    console.log("dipped:", dippedField);

    if(prevField)
    {
        prevField.value = data.om;
    }

    if(registerField)
    {
        registerField.value = data.or;
    }

    if(dippedField)
    {
        dippedField.value = data.dip;
    }

    let elapsed =
        Date.now() - startTime;

    let remaining =
        Math.max(0, 2000 - elapsed);

    setTimeout(function(){

        hideLoad();

    }, remaining);

})

   .catch(error => {

    console.error(error);

    let elapsed =
        Date.now() - startTime;

    let remaining =
        Math.max(0, 1000 - elapsed);

    setTimeout(function(){

        hideLoad();

	showRecordErrorPopup();

    }, remaining);

});
}
</script>
<script>
function showRecordErrorPopup()
{
    document.getElementById(
        "recordErrorPopup"
    ).style.display = "flex";
}

function closeRecordErrorPopup()
{
    document.getElementById(
        "recordErrorPopup"
    ).style.display = "none";
}
</script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    document.querySelectorAll(
        "#dailyRecordForm input"
    ).forEach(function(input){

        input.addEventListener(
            "focus",
            function(){

                if(
                    this.value === "0" ||
                    this.value === "00" ||
                    this.value === "000"
                )
                {
                    this.select();
                }

            }
        );

    });

});
</script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    document.querySelectorAll(
        "#dailyRecordForm input[data-default]"
    ).forEach(function(input){

        input.addEventListener("blur", function(){

            if(this.value.trim() === "")
            {
                this.value =
                    this.dataset.default;
            }

        });

    });

});
</script>

<script>

function searchDailyRecords()
{
    showLoad();

    let form =
        document.getElementById(
            "searchDailyRecordsForm"
        );

    let formData =
        new FormData(form);

    fetch(
        "search_daily_records.php",
        {
            method: "POST",
            body: formData
        }
    )

    .then(response => response.text())

.then(data => {

    console.log("Returned data:");
    console.log(data);

    document.getElementById(
        "dailyRecordsResults"
    ).style.display = "block";

    document.getElementById(
        "dailyRecordsTableContainer"
    ).innerHTML = data;

    hideLoad();
})
    .catch(error => {

    hideLoad();

    console.error(error);

    alert(
        "Failed to load records: " +
        error.message
    );
});
console.log(
    document.getElementById(
        "dailyRecordsResults"
    )
);
}

</script>
<script>

function openBirthPopup()
{
    document.getElementById(
        "birthPopup"
    ).style.display = "flex";
}

function closeBirthPopup()
{
    document.getElementById(
        "birthPopup"
    ).style.display = "none";
}

</script>
<script>

function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll(".row-check");
    checkboxes.forEach(cb => cb.checked = source.checked);
}

function hideSelectedRows() {

    const selectedIds = [];

    document.querySelectorAll('.row-check:checked').forEach(cb => {
        selectedIds.push(cb.dataset.id);
    });

    if (selectedIds.length === 0) {
        document.getElementById("noSelectionPopup").style.display = "flex";
        return;
    }

    fetch("hide_selected.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            ids: selectedIds
        })
    })
    .then(response => response.json())
    .then(data => {

        console.log("Server response:", data);

        if (data.success) {

            document.getElementById("hideSuccessPopup").style.display = "flex";

            selectedIds.forEach(id => {
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.remove();
                }
            });

        } else {

            alert(data.message || "Failed to hide records");

        }

    })
    .catch(error => {

        console.error(error);
        alert("An error occurred while hiding records.");

    });

}
</script>
<script>
function openHideConfirmPopup() {

    const selectedIds = document.querySelectorAll('.row-check:checked');

    if (selectedIds.length === 0) {
        openNoSelectionPopup();
        return;
    }

    activeHideTab = "cattle";

    document.getElementById("hideConfirmPopup").style.display = "flex";
}
function openBirthHideConfirmPopup() {

    const selectedIds = document.querySelectorAll('.birth-row-check:checked');

    if (selectedIds.length === 0) {
        openNoSelectionPopup();
        return;
    }

    activeHideTab = "births";

    document.getElementById("birthHideConfirmPopup").style.display = "flex";
}

function confirmBirthHideSelected() {

    closeBirthHideConfirmPopup();

    document.getElementById("loadingPoup").style.display = "flex";

    hideBirthRecords();
}

function openHideSuccessPopup() {
    document.getElementById("hideSuccessPopup").style.display = "flex";
refreshAllTables();
}

function closeHideSuccessPopup() {

    document.getElementById("hideSuccessPopup").style.display = "none";

    const tab = sessionStorage.getItem("activeHideTab");

    if (tab) {
        localStorage.setItem("activeTab", tab);
        sessionStorage.removeItem("activeHideTab");
    }

    // ❌ REMOVE PAGE RELOAD
    // location.reload();

    // ✅ Instead refresh ONLY the affected table
    if (tab === "birthTab") {
        refreshTable("fetch_births.php", "birthRecords");
    }

    if (tab === "permitInTab") {
        refreshTable("fetch_permits_in.php", "permitInRecords");
    }

    if (tab === "deathTab") {
        refreshTable("fetch_deaths.php", "deathRecords");
    }

    if (tab === "masterTab") {
        refreshTable("fetch_master_register.php", "masterRegisterRecords");
    }

}

function openNoSelectionPopup() {
    document.getElementById("noSelectionPopup").style.display = "flex";
}

function closeNoSelectionPopup() {
    document.getElementById("noSelectionPopup").style.display = "none";
}
</script>
<script>
function hideBirthRecords() {

    const selectedIds = [];

    document.querySelectorAll(".birth-row-check:checked")
        .forEach(cb => {
            selectedIds.push(cb.dataset.id);
        });

    console.log("Selected IDs:", selectedIds);

    fetch("hide_birth_records.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            ids: selectedIds
        })
    })
    .then(response => response.text())
    .then(text => {

        console.log("Server response:", text);

        const data = JSON.parse(text);

        document.getElementById("loadingPoup").style.display = "none";

        if (data.success) {

            // Remove the hidden rows immediately
            selectedIds.forEach(id => {

                const row = document.querySelector(`tr[data-id="${id}"]`);

                if (row) {
                    row.remove();
                }

            });

            // Then show the success popup
            openHideSuccessPopup();

        } else {

            alert(data.message || "Failed to hide records");

        }

    })
    .catch(error => {

        document.getElementById("loadingPoup").style.display = "none";

        console.error("Error:", error);

    });

}
function refreshBirthRecords() {

    fetch("fetch_births.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("birthRecords").innerHTML = html;
        })
        .catch(error => console.error(error));

}
function openBirthHideConfirmPopup() {

    const selectedIds = document.querySelectorAll(
        ".birth-row-check:checked"
    );

    if (selectedIds.length === 0) {

        openNoSelectionPopup();
        return;
    }

    document.getElementById(
        "birthHideConfirmPopup"
    ).style.display = "flex";
}
function openBirthHideConfirmPopup() {

    const selectedIds = document.querySelectorAll(
        ".birth-row-check:checked"
    );

    if (selectedIds.length === 0) {
        openNoSelectionPopup();
        return;
    }

    document.getElementById(
        "birthHideConfirmPopup"
    ).style.display = "flex";
}

function closeBirthHideConfirmPopup() {

    document.getElementById(
        "birthHideConfirmPopup"
    ).style.display = "none";
}

function confirmBirthHideSelected() {

    closeBirthHideConfirmPopup();

    document.getElementById(
        "loadingPoup"
    ).style.display = "flex";

    hideBirthRecords();
}
function confirmHideSelected() {

    // Save current tab so it can be restored after reload
    const activeTab = document.querySelector(".tab-content.active");

    if (activeTab) {
        sessionStorage.setItem(
            "activeHideTab",
            activeTab.id
        );
    }

    // Close confirmation popup
    closeHideConfirmPopup();

    // Show loading popup
    document.getElementById(
        "loadingPoup"
    ).style.display = "flex";

    // Hide selected records
    hideSelectedRows();
function closeHideConfirmPopup() {

    document.getElementById(
        "hideConfirmPopup"
    ).style.display = "none";

}
}
</script>
<script>
function openFatePopup() {

    document.getElementById(
        "fatePopup"
    ).style.display = "flex";

}

function closeFatePopup() {

    document.getElementById(
        "fatePopup"
    ).style.display = "none";

}
</script>
<script>
function searchFateTable() {

    let filter = document
        .getElementById("fateSearch")
        .value
        .toUpperCase();

    let rows = document.querySelectorAll(
        "#fateRecords tr"
    );

    rows.forEach(row => {

        let animalId =
            row.cells[0]?.textContent
            .toUpperCase() || "";

        if (animalId.includes(filter)) {

            row.style.display = "";

        } else {

            row.style.display = "none";
        }
    });
}
</script>
<script>

function openPermitInPopup(){

    document.getElementById(
        "permitInPopup"
    ).style.display = "flex";
}

function closePermitInPopup(){

    document.getElementById(
        "permitInPopup"
    ).style.display = "none";
}

</script>
<script>
function toggleTagMenu(){
    document.getElementById("tagMenu").classList.toggle("show");
}

/* close when clicking outside */
document.addEventListener("click", function(e){
    const dropdown = document.querySelector(".tag-dropdown");
    const menu = document.getElementById("tagMenu");

    if(!dropdown.contains(e.target)){
        menu.classList.remove("show");
    }
});
</script>
<script>
function loadEartagRequests()
{
    fetch('request_eartags.php')
    .then(response => response.text())
    .then(data =>
    {
        document.getElementById('eartagRequestBody').innerHTML = data;

        document.getElementById('requestEartagPopup').style.display = 'flex';

        // Attach Select All event
        const selectAll = document.getElementById('selectAllTags');

        if(selectAll)
        {
            selectAll.onchange = function()
            {
                document.querySelectorAll('.animal-checkbox')
                .forEach(function(box)
                {
                    box.checked = selectAll.checked;
                });
            };
        }
    })
    .catch(error => console.error('Error loading eartag requests:', error));
}

function closeRequestEartagPopup()
{
    document.getElementById('requestEartagPopup').style.display = 'none';
}
</script>
<script>
function searchEartagTable() {

    let filter = document
        .getElementById("eartagSearch")
        .value
        .toUpperCase();

    let rows = document.querySelectorAll(
        "#eartagRequestBody tr"
    );

    rows.forEach(row => {

        let text =
            row.textContent.toUpperCase();

        row.style.display =
            text.includes(filter)
            ? ""
            : "none";

    });
}
</script>
<script>

function generateTagRequest()
{
    let checkboxes =
        document.querySelectorAll(
            '.animal-checkbox'
        );

    console.log(
        "Total checkboxes:",
        checkboxes.length
    );

    let selected = [];

    checkboxes.forEach(cb =>
    {
        console.log(
            "Value:",
            cb.value,
            "Checked:",
            cb.checked
        );

        if(cb.checked)
        {
            selected.push(cb.value);
        }
    });

    console.log(
        "Selected:",
        selected
    );

    if(selected.length === 0)
    {
        alert(
            'Select at least one animal'
        );
        return;
    }

    showLoadingPopup();

    fetch('save_eartag_request.php', {

        method: 'POST',

        headers: {
            'Content-Type':
            'application/json'
        },

        body: JSON.stringify(selected)

    })
    .then(res => res.text())
    .then(msg =>
    {
        hideLoadingPopup();

        alert(msg);

        location.reload();
    })
    .catch(error =>
    {
        hideLoadingPopup();

        console.error(error);

        alert('Error saving request');
    });

} // <-- THIS WAS MISSING


function showLoadingPopup()
{
    document.getElementById(
        'loadingPoup'
    ).style.display = 'flex';
}

function hideLoadingPopup()
{
    document.getElementById(
        'loadingPoup'
    ).style.display = 'none';
}

</script>
<script>
function loadApplyEartags()
{
    fetch('load_approved_eartags.php')

    .then(response => response.text())

    .then(data =>
    {
        document.getElementById(
            'applyEartagBody'
        ).innerHTML = data;

        document.getElementById(
            'applyEartagPopup'
        ).style.display = 'flex';

        const selectAll =
            document.getElementById(
                'selectAllApplyTags'
            );

        if(selectAll)
        {
            selectAll.onchange =
            function()
            {
                document
                .querySelectorAll(
                    '.apply-checkbox'
                )
                .forEach(box =>
                {
                    box.checked =
                    selectAll.checked;
                });
            };
        }
    });
}
function closeApplyEartagPopup()
{
    document.getElementById(
        'applyEartagPopup'
    ).style.display = 'none';
}
function searchApplyEartagTable()
{
    let filter =
        document
        .getElementById(
            'applyTagSearch'
        )
        .value
        .toUpperCase();

    let rows =
        document.querySelectorAll(
            '#applyEartagBody tr'
        );

    rows.forEach(row =>
    {
        let eartag =
            row.cells[1]
            ?.textContent
            .toUpperCase() || '';

        row.style.display =
            eartag.includes(filter)
            ? ''
            : 'none';
    });
}
function applySelectedEartags()
{
    let selected = [];

    document
    .querySelectorAll(
        '.apply-checkbox:checked'
    )
    .forEach(box =>
    {
        selected.push(
            box.value
        );
    });

    if(selected.length === 0)
    {
        alert(
            'Select at least one eartag.'
        );

        return;
    }

    fetch(
        'apply_eartags.php',
        {
            method:'POST',

            headers:{
                'Content-Type':
                'application/json'
            },

            body:JSON.stringify(
                selected
            )
        }
    )

    .then(response =>
        response.text()
    )

    .then(msg =>
    {
        alert(msg);

        closeApplyEartagPopup();

        location.reload();
    });
}

</script>
<script>
document.addEventListener('DOMContentLoaded', function()
{
    const birthForm =
        document.getElementById('birthForm');

    if(!birthForm)
    {
        console.error(
            'birthForm not found'
        );
        return;
    }

    birthForm.addEventListener(
        'submit',
        function(e)
        {
            e.preventDefault();

            showLoadingPopup();

            let formData =
                new FormData(this);

            fetch('save_birth.php',
            {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data =>
            {
                hideLoadingPopup();

                data = data.trim();

                console.log(
                    "Server response:",
                    data
                );

                /* KRAAL NOT FOUND */
                if(data === 'KRAAL_NOT_FOUND')
                {
                    showErrorPopup(
                        'Kraal does not exist.'
                    );
                    return;
                }

                /* DAM NOT FOUND */
                if(data === 'DAM_NOT_FOUND')
                {
                    showErrorPopup(
                        'Dam does not exist in Master Register.'
                    );
                    return;
                }

                /* SUCCESS */
                if(data.startsWith('Success'))
                {
                    closeBirthPopup();

                    showSuccessPopup(data);

                    return;
                }

                /* OTHER ERRORS */
                showErrorPopup(data);
            })
            .catch(error =>
            {
                hideLoadingPopup();

                console.error(error);

                showErrorPopup(
                    'An error occurred while saving.'
                );
            });
        }
    );
});
</script>
<script>
function showLoadingPopup()
{
    document.getElementById('loadingPoup')
        .style.display = 'flex';
}

function hideLoadingPopup()
{
    document.getElementById('loadingPoup')
        .style.display = 'none';
}
function closeDamError()
{
    document.getElementById('damErrorPopup')
        .style.display = 'none';
}

</script>
<script>
document.addEventListener('DOMContentLoaded', function()
{
    const permitInForm = document.querySelector('#permitInForm');

    if(!permitInForm)
    {
        console.error(
            'permitInForm not found'
        );
        return;
    }

    permitInForm.addEventListener(
        'submit',
        function(e)
        {
            e.preventDefault();

            showLoadingPopup();

            let formData =
                new FormData(this);

            fetch('save_permit_in.php',
            {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data =>
            {
                hideLoadingPopup();

                data = data.trim();

                console.log(
                    'Server response:',
                    data
                );

                /* KEEP FORM OPEN */
                if(data === 'ANIMAL_EXISTS')
                {
                    showErrorPopup(
                        'Animal already exists in system'
                    );
                    return;
                }

                if(data === 'KRAAL_NOT_FOUND')
                {
                    showErrorPopup(
                        'Kraal does not exist'
                    );
                    return;
                }

                if(data === 'ANIMAL_NOT_FOUND')
                {
                    showErrorPopup(
                        'Animal does not exist in master register'
                    );
                    return;
                }

                /* CLOSE FORM ONLY ON SUCCESS */
                if(data.startsWith('Success'))
                {
                    closePermitInPopup();

                    showSuccessPopup(data);

                    return;
                }

                showErrorPopup(data);
            })
            .catch(error =>
            {
                hideLoadingPopup();

                console.error(error);

                showErrorPopup(
                    'System error occurred'
                );
            });
        }
    );
});
document.querySelector("#permitInRecords").innerHTML = "";
fetch("fetch_permits_in.php")
    .then(res => res.text())
    .then(html => {
        document.querySelector("#permitInRecords").innerHTML = html;
    });
</script>
<script>

function showErrorPopup(message)
{
    document.querySelector('#errorPoup .popup-body')
        .innerHTML =
        `<div class="popup-icon error-icon">✖</div>${message}`;

    document.getElementById('errorPoup')
        .style.display = 'flex';
}

function closeError()
{
    document.getElementById('errorPoup')
        .style.display = 'none';
}

function openPermitOutPopup()
{
    document.getElementById('permitOutPopup').style.display = 'flex';
}

function closePermitOutPopup()
{
    document.getElementById('permitOutPopup').style.display = 'none';
}

</script>
<script>
document.addEventListener('DOMContentLoaded', function()
{
    const form = document.getElementById('permitOutForm');

    console.log("Permit form found:", form);

    if(!form) return;

    form.addEventListener('submit', function(e)
    {
        e.preventDefault();

        showLoadingPopup();

        fetch('save_permit_out.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(res => res.text())
        .then(data =>
        {
            hideLoadingPopup();

            data = data.trim();

            if(data === "ANIMAL_NOT_FOUND")
                return showErrorPopup("Animal not found");

            if(data === "KRAAL_NOT_FOUND")
                return showErrorPopup("Kraal not found");

            if(data.startsWith("Success"))
            {
                closePermitOutPopup();
                showSuccessPopup(data);
            }
        })
        .catch(err =>
        {
            hideLoadingPopup();
            showErrorPopup("System error");
            console.error(err);
        });
    });
});
</script>
<script>
function openDeathPopup()
{
    document.getElementById(
        'deathPopup'
    ).style.display = 'flex';
}

function closeDeathPopup()
{
    document.getElementById(
        'deathPopup'
    ).style.display = 'none';
}
</script>
<script>
document.addEventListener(
'DOMContentLoaded',
function()
{
    const deathForm =
        document.getElementById('deathForm');

    if(!deathForm) return;

    deathForm.addEventListener(
    'submit',
    function(e)
    {
        e.preventDefault();

        showLoadingPopup();

        fetch(
            'save_death.php',
            {
                method:'POST',
                body:new FormData(this)
            }
        )
        .then(res => res.text())
        .then(data =>
        {
            hideLoadingPopup();

            data = data.trim();

            console.log(
                "Server response:",
                data
            );

            if(data ===
                'ANIMAL_NOT_FOUND')
            {
                showErrorPopup(
                    'Animal does not exist in Master Register'
                );
                return;
            }

            if(data ===
                'KRAAL_NOT_FOUND')
            {
                showErrorPopup(
                    'Kraal does not exist'
                );
                return;
            }

            if(data.startsWith(
                'Success'))
            {
                closeDeathPopup();

                showSuccessPopup(
                    data
                );

                return;
            }

            showErrorPopup(data);
        })
        .catch(error =>
        {
            hideLoadingPopup();

            console.error(error);

            showErrorPopup(
                'System error occurred'
            );
        });

    });
});
</script>
<script>

function hidePermitInRecords() {

    const selectedIds = [];

    document.querySelectorAll(".permitin-row-check:checked")
        .forEach(cb => {
            selectedIds.push(cb.dataset.id);
        });

    console.log("Selected IDs:", selectedIds);

    fetch("hide_permitin_records.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            ids: selectedIds
        })
    })

    .then(response => response.text())
    .then(text => {

        console.log("Server response:", text);

        const data = JSON.parse(text);

        document.getElementById("loadingPoup").style.display = "none";

        if (data.success) {

            // Hide rows instantly in UI
            selectedIds.forEach(id => {
                const checkbox = document.querySelector(`.permitin-row-check[data-id="${id}"]`);

                if (checkbox) {
                    const row = checkbox.closest("tr");
                    if (row) {
                        row.style.display = "none";
                    }
                }
            });

            openHideSuccessPopup();

        } else {
            alert(data.message || "Failed to hide records");
        }
    })

    .catch(error => {

        document.getElementById("loadingPoup").style.display = "none";

        console.error("Error:", error);

    });

}


function openPermitInHideConfirmPopup() {

    const selectedIds = document.querySelectorAll(".permitin-row-check:checked");

    if (selectedIds.length === 0) {
        openNoSelectionPopup();
        return;
    }

    document.getElementById("permitInHideConfirmPopup").style.display = "flex";
}


function closePermitInHideConfirmPopup() {
    document.getElementById("permitInHideConfirmPopup").style.display = "none";
}


function confirmPermitInHideSelected() {

    const activeTab = document.querySelector(".tab-content.active");

    if (activeTab) {
        sessionStorage.setItem("activeHideTab", activeTab.id);
    }

    closePermitInHideConfirmPopup();

    document.getElementById("loadingPoup").style.display = "flex";

    hidePermitInRecords();
}

</script>
<script>
function refreshAllTables() {

    // Births
    fetch("fetch_births.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("birthRecords").innerHTML = html;
        });

    // Permit In
    fetch("fetch_permits_in.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("permitInRecords").innerHTML = html;
        });

    // Deaths
    fetch("fetch_deaths.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("deathRecords").innerHTML = html;
        });

    // Master Register
    fetch("fetch_master_register.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("masterRecords").innerHTML = html;
        });

    // Permit Out
    fetch("fetch_permits_out.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("permitOutRecords").innerHTML = html;
        });
// Fate
    fetch("fetch_fate_register.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("fateRecords").innerHTML = html;
        });

}
function refreshTable(url, tbodyId) {

    fetch(url)
        .then(res => res.text())
        .then(html => {
            document.getElementById(tbodyId).innerHTML = html;
        })
        .catch(err => console.error(err));

}
function toggleSelectColumn(tabId, show) {

    // Get the tab container
    const tab = document.getElementById(tabId);

    // Safety check (prevents null errors)
    if (!tab) {
        console.error("Tab not found: " + tabId);
        return;
    }

    // Find all select columns ONLY inside this tab
    const selectCells = tab.querySelectorAll(".select-col");

    // Show or hide each cell
    selectCells.forEach(cell => {
        cell.style.display = show ? "" : "none";
    });

}
function togglePermitOutDeleteMode(){

    
    document
    .getElementById("permitoutWrapper")
    .classList.toggle("delete-mode");

    const bar = document.getElementById("permitOutDeleteBar");

    if(bar.style.display=="flex"){

        bar.style.display="none";

    }else{

        bar.style.display="flex";

    }

}
function togglePermitOutSelectAll(source){

    document.querySelectorAll(".permitout-row-check")
        .forEach(cb => cb.checked = source.checked);

}
</script>
<script>
function hidePermitOutRecords() {

    const selectedIds = [];

    document.querySelectorAll(".permitout-row-check:checked")
        .forEach(cb => {
            selectedIds.push(cb.dataset.id);
        });

    console.log("Selected IDs:", selectedIds);

    fetch("hide_permitout_records.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            ids: selectedIds
        })
    })

    .then(response => response.text())

    .then(text => {

        console.log("Server response:", text);

        const data = JSON.parse(text);

        document.getElementById("loadingPoup").style.display = "none";

        if (data.success) {

            openHideSuccessPopup();

        } else {

            alert(data.message || "Failed to hide records");

        }

    })

    .catch(error => {

        document.getElementById("loadingPoup").style.display = "none";

        console.error("Error:", error);

    });

}


function openPermitOutHideConfirmPopup() {

    const selectedIds = document.querySelectorAll(
        ".permitout-row-check:checked"
    );

    if (selectedIds.length === 0) {

        openNoSelectionPopup();
        return;

    }

    document.getElementById(
        "permitOutHideConfirmPopup"
    ).style.display = "flex";

}


function closePermitOutHideConfirmPopup() {

    document.getElementById(
        "permitOutHideConfirmPopup"
    ).style.display = "none";

}


function confirmPermitOutHideSelected() {

    const activeTab = document.querySelector(".tab-content.active");

    if (activeTab) {

        sessionStorage.setItem(
            "activeHideTab",
            activeTab.id
        );

    }

    closePermitOutHideConfirmPopup();

    document.getElementById(
        "loadingPoup"
    ).style.display = "flex";

    hidePermitOutRecords();

}
</script>
<script>
function updatePresence() {
    fetch('update_status.php', {
        method: 'POST',
        credentials: 'include'
    });
}

// send every 20 seconds
setInterval(updatePresence, 20000);

// also send immediately on page load
updatePresence();

setInterval(() => {
    fetch('presence_cleanup.php');
}, 60000);
</script>
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
<script>

const birthForm = document.getElementById("birthForm");

if(birthForm){

    birthForm.addEventListener("submit", function(e){

        // STOP PAGE RELOAD
        e.preventDefault();

        // SHOW LOADING
        document.getElementById("loadingPopup").style.display = "flex";

        // FORM DATA
        let formData = new FormData(birthForm);

        // SEND TO PHP
        fetch("contact4.php", {

            method: "POST",
            body: formData

        })

        .then(response => response.text())

        .then(data => {

            // HIDE LOADING
            document.getElementById("loadingPopup").style.display = "none";

            // SUCCESS
            if(data.trim() === "success"){

                // SHOW SUCCESS POPUP
                document.getElementById("successPopup").style.display = "flex";

                // RESET FORM
                birthForm.reset();

refreshAllRegisters();

                // KEEP BIRTH TAB ACTIVE
                document.querySelectorAll(".tab-content").forEach(tab=>{

                    tab.style.display = "none";
                    tab.classList.remove("active");

                });

                document.querySelectorAll(".tab-btn").forEach(btn=>{

                    btn.classList.remove("active");

                });

                document.getElementById("births").style.display = "block";
                document.getElementById("births").classList.add("active");

                document.querySelector('[data-tab="births"]').classList.add("active");

                // RELOAD RECORDS TABLE
                loadBirthRecords();

            }

            // ERROR
            else{

                document.getElementById("errorPopup").style.display = "flex";

            }

        })

        .catch(error => {

            document.getElementById("loadingPopup").style.display = "none";

            document.getElementById("errorPopup").style.display = "flex";

        });

    });

}

/* LOAD UPDATED RECORDS */

function loadBirthRecords(){

    fetch("fetch_births.php")

    .then(response => response.text())

    .then(data => {

        document.getElementById("birthRecords").innerHTML = data;

    });

}

function loadPermitOutRecords(){

    fetch("fetch_permits_out.php")

    .then(response => response.text())

    .then(data => {

        document.getElementById("permitOutRecords").innerHTML = data;

    });

}

/* CLOSE POPUPS */

function closeSuccessPopup(){

    document.getElementById("successPopup").style.display = "none";

}

function closeErrorPopup(){

    document.getElementById("errorPopup").style.display = "none";

}

function closeExistsPopup(){

    document.getElementById("existsPopup").style.display = "none";

}

const urlParams = new URLSearchParams(window.location.search);

const status = urlParams.get("status");

const activeTab = urlParams.get("tab");

/* OPEN SPECIFIC TAB */

if(activeTab){

    document.querySelectorAll(".tab-content").forEach(tab => {

        tab.style.display = "none";
        tab.classList.remove("active");

    });

    document.querySelectorAll(".tab-btn").forEach(btn => {

        btn.classList.remove("active");

    });

    const selectedTab = document.getElementById(activeTab);

    if(selectedTab){

        selectedTab.style.display = "block";
        selectedTab.classList.add("active");

    }

    const selectedButton =
    document.querySelector(`[data-tab="${activeTab}"]`);

    if(selectedButton){

        selectedButton.classList.add("active");

    }

}

/* SUCCESS POPUP */

if(status === "success"){

    document.getElementById("successPopup").style.display = "flex";

}

/* DUPLICATE POPUP */

if(status === "exists"){

    document.getElementById("existsPopup").style.display = "flex";

}

</script>
<script>

const permitOutForm = document.getElementById("permitOutForm");
console.log("Permit form found:", permitOutForm);

if(permitOutForm){

    permitOutForm.addEventListener("submit", function(e){

        // STOP NORMAL SUBMIT
        e.preventDefault();

        // SHOW LOADING
        document.getElementById("loadingPopup").style.display = "flex";

        // FORM DATA
        let formData = new FormData(permitOutForm);

        // SEND TO PHP
        fetch("contact7.php", {

            method: "POST",
            body: formData

        })

        .then(response => response.text())

        .then(data => {

            // HIDE LOADING
            document.getElementById("loadingPopup").style.display = "none";

            data = data.trim();

            // SUCCESS
if(data === "success"){

    document.getElementById("successPopup")
        .style.display = "flex";

    permitOutForm.reset();

    loadPermitOutRecords();
	
refreshAllRegisters();

}

            // DUPLICATE
            else if(data === "duplicate"){

                document.getElementById("duplicatePopup")
                    .style.display = "flex";

            }

            // NOT FOUND
            else if(data === "notfound"){

                document.getElementById("notFoundPopup")
                    .style.display = "flex";

            }

            // ERROR
            else{

                document.getElementById("errorPopup")
                    .style.display = "flex";

            }

            // KEEP PERMIT OUT TAB ACTIVE
            document.querySelectorAll(".tab-content").forEach(tab => {

                tab.style.display = "none";
                tab.classList.remove("active");

            });

            document.querySelectorAll(".tab-btn").forEach(btn => {

                btn.classList.remove("active");

            });

            document.getElementById("permitsout").style.display = "block";
            document.getElementById("permitsout").classList.add("active");

            document.querySelector('[data-tab="permitsout"]')
                .classList.add("active");

        })

        .catch(error => {

            document.getElementById("loadingPopup").style.display = "none";

            document.getElementById("errorPopup")
                .style.display = "flex";

        });

    });

}

/* CLOSE POPUPS */

function closeNotFoundPopup(){

    document.getElementById("notFoundPopup").style.display = "none";

}

function closeDuplicatePopup(){

    document.getElementById("duplicatePopup").style.display = "none";

}

</script>
<script>

let currentAnimal = {};

/* =========================
   OPEN ANIMAL PROFILE
========================= */

function openAnimalPopup(
    id,
    animal_id,
    birth_date,
    sex,
    breed,
    color,
    kraal,
    diptank,
    registration,
    source,
    alterations,
    Tenure
){

    /* STORE CURRENT ANIMAL */

    currentAnimal = {
        id,
        animal_id,
        birth_date,
        sex,
        breed,
        color,
        kraal,
        diptank,
	Tenure,
        registration,
        source,
        alterations
    };

    /* POPULATE PROFILE POPUP */

    document.getElementById("profileAnimalID").innerText = animal_id;
    document.getElementById("profileBirthDate").innerText = birth_date;
    document.getElementById("profileSex").innerText = sex;
    document.getElementById("profileBreed").innerText = breed;
    document.getElementById("profileColor").innerText = color;
    document.getElementById("profileKraal").innerText = kraal;
    document.getElementById("profileDiptank").innerText = diptank;
    document.getElementById("profileTenure").innerText = Tenure;
    document.getElementById("profileSource").innerText = source;
    document.getElementById("profileAlterations").innerText = alterations;

    document.getElementById("animalProfilePopup").style.display = "flex";
}

/* =========================
   CLOSE PROFILE POPUP
========================= */

function closeAnimalPopup(){

    document.getElementById("animalProfilePopup").style.display = "none";

}

/* =========================
   EDIT BUTTON
========================= */

document.addEventListener("click", function(e){

    if(e.target.id === "editAnimalBtn"){

        document.getElementById("editID").value =
            currentAnimal.id;

        document.getElementById("editAnimalID").value =
            currentAnimal.animal_id;

        document.getElementById("editBirthDate").value =
            currentAnimal.birth_date;

        document.getElementById("editSex").value =
            currentAnimal.sex;

        document.getElementById("editBreed").value =
            currentAnimal.breed;

        document.getElementById("editColor").value =
            currentAnimal.color;

        document.getElementById("editKraal").value =
            currentAnimal.kraal;

        document.getElementById("editDiptank").value =
            currentAnimal.diptank;

        document.getElementById("editTenure").value =
            currentAnimal.Tenure;

        document.getElementById("editSource").value =
            currentAnimal.source;

        document.getElementById("editAlterations").value =
            currentAnimal.alterations;

        document.getElementById("editAnimalPopup")
            .style.display = "flex";
    }

});

/* =========================
   CLOSE EDIT POPUP
========================= */

function closeEditAnimalPopup(){

    document.getElementById("editAnimalPopup")
        .style.display = "none";

}

/* =========================
   MEDICAL HISTORY BUTTON
========================= */

const medicalHistoryBtn =
    document.getElementById("medicalHistoryBtn");

if(medicalHistoryBtn){

    medicalHistoryBtn.addEventListener("click", function(){

        window.location.href =
            "medical_history.php?animal_id="
            + currentAnimal.animal_id;

    });

}

window.addEventListener("load", function(){

    const params =
        new URLSearchParams(window.location.search);

    if(params.get("tab") === "master"){

        document.querySelectorAll(".tab-content")
        .forEach(tab => {

            tab.style.display = "none";
            tab.classList.remove("active");

        });

        document.querySelectorAll(".tab-btn")
        .forEach(btn => {

            btn.classList.remove("active");

        });

        document.getElementById("master")
            .style.display = "block";

        document.getElementById("master")
            .classList.add("active");

        document.querySelector('[data-tab="master"]')
            .classList.add("active");

    }

});

</script>
<script>

const tabs = document.querySelectorAll(".tab-btn");

tabs.forEach(tab => {

    tab.addEventListener("click", () => {

        const target = tab.getAttribute("data-tab");

        // REMOVE ACTIVE
        document.querySelectorAll(".tab-btn").forEach(btn => {
            btn.classList.remove("active");
        });

        document.querySelectorAll(".tab-content").forEach(content => {
            content.classList.remove("active");
            content.style.display = "none";
        });

        // ACTIVATE BUTTON
        tab.classList.add("active");

        // ACTIVATE TARGET TAB
        const activeTab = document.getElementById(target);

        if(activeTab){

            activeTab.style.display = "block";
            activeTab.classList.add("active");

        }

    });

});

</script>

<script>


    // SUCCESS POPUP
    function showSuccessPopup(){

        document.getElementById("loadingPopup").style.display = "none";

        document.getElementById("successPopup").style.display = "flex";
 // Refresh all records
    refreshAllTables();
    }

    // ERROR POPUP
    function showErrorPopup(){

        document.getElementById("loadingPopup").style.display = "none";

        document.getElementById("errorPopup").style.display = "flex";
    }

    // CLOSE SUCCESS
    function closeSuccessPopup(){

        document.getElementById("successPopup").style.display = "none";

    }

    // CLOSE ERROR
    function closeErrorPopup(){

        document.getElementById("errorPopup").style.display = "none";

    }

</script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    const deathForm = document.getElementById("deathForm");

    if(deathForm){

        deathForm.addEventListener("submit", function(e){

            e.preventDefault();

            document.getElementById("loadingPopup").style.display = "flex";

            let formData = new FormData(this);

            fetch("contact_death.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {

                document.getElementById("loadingPopup").style.display = "none";

if(data.trim() === "success"){

    document.getElementById("successPopup").style.display = "flex";

    deathForm.reset();

refreshAllRegisters();

    loadDeathRecords();
                    // KEEP DEATH TAB OPEN
                    document.querySelectorAll(".tab-content").forEach(tab=>{
                        tab.style.display = "none";
                        tab.classList.remove("active");
                    });

                    document.querySelectorAll(".tab-btn").forEach(btn=>{
                        btn.classList.remove("active");
                    });

                    document.getElementById("deaths").style.display = "block";
                    document.getElementById("deaths").classList.add("active");

                    document.querySelector('[data-tab="deaths"]')
                        .classList.add("active");

                }else{

                    document.getElementById("errorPopup").style.display = "flex";

                }

            })
            .catch(error => {

                document.getElementById("loadingPopup").style.display = "none";
                document.getElementById("errorPopup").style.display = "flex";

            });

        });
     function loadDeathRecords(){

    fetch("fetch_deaths.php")
    .then(response => response.text())
    .then(data => {

        document.getElementById("deathRecords").innerHTML = data;

    });

}
    }

});
</script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    const editForm = document.getElementById("editAnimalForm");

    console.log("Form found:", editForm);

    if(editForm){

        editForm.addEventListener("submit", function(e){

            e.preventDefault();

            console.log("Submit intercepted");

            let formData = new FormData(this);
		console.log("Updating row ID:", document.getElementById("editID").value);

            fetch("update_animal.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {

                console.log("Server response:", data);

                if(data.trim() === "success"){

                    closeEditAnimalPopup();
                    closeAnimalPopup();

                    loadMasterRecords();
			loadFateRecords();

document.getElementById("successPopup")
    .style.display = "flex";

                }else{

                    alert("Server returned: " + data);

                }

            })
            .catch(error => {

                console.log("Fetch error:", error);

                alert("Update failed");

            });

        });

    }else{

        console.log("editAnimalForm NOT FOUND");

    }

});
</script>
<script>
const permitInForm = document.getElementById("permitInForm");

if(permitInForm){

    permitInForm.addEventListener("submit", function(e){

        e.preventDefault();

        // SHOW LOADING POPUP
        document.getElementById("loadingPopup").style.display = "flex";

        let formData = new FormData(this);

        fetch("contact6.php", {
            method: "POST",
            body: formData
        })

        .then(response => response.text())

        .then(data => {

            // HIDE LOADING POPUP
            document.getElementById("loadingPopup").style.display = "none";

            data = data.trim();

            if(data === "success"){

                document.getElementById("successPopup")
                    .style.display = "flex";

		refreshAllRegisters();

                permitInForm.reset();

                // KEEP PERMITS IN TAB ACTIVE
                document.querySelectorAll(".tab-content").forEach(tab => {
                    tab.style.display = "none";
                    tab.classList.remove("active");
                });

                document.querySelectorAll(".tab-btn").forEach(btn => {
                    btn.classList.remove("active");
                });

                document.getElementById("permitsin").style.display = "block";
                document.getElementById("permitsin").classList.add("active");

                document.querySelector('[data-tab="permitsin"]')
                    .classList.add("active");

            }else{

                document.getElementById("errorPopup")
                    .style.display = "flex";

            }

        })

        .catch(error => {

            document.getElementById("loadingPopup").style.display = "none";

            document.getElementById("errorPopup")
                .style.display = "flex";

        });

    });

}
function loadMasterRecords(){

    fetch("fetch_master_register.php")

    .then(response => response.text())

    .then(data => {

        document.getElementById("masterRecords").innerHTML = data;

    });

}

function loadFateRecords(){

    fetch("fetch_fate_register.php")

    .then(response => response.text())

    .then(data => {

        document.getElementById("fateRecords").innerHTML = data;

    });

}
function refreshAllRegisters(){

    loadMasterRecords();
    loadFateRecords();

}
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

</script>
<script>

function toggleMessagesPanel(){

    const panel = document.getElementById("messagesPanel");

    console.log("messagesPanel =", panel);

    if(!panel){
        console.error("messagesPanel not found");
        return;
    }

    panel.classList.toggle("active");

    savePanelState();
}
</script>
<script>
function viewNote(title, content, date)
{
    document.getElementById("viewNoteTitle").innerText = title;

    document.getElementById("viewNoteContent").innerText =
        content;

    document.getElementById("viewNoteDate").innerText =
        "Created: " + date;

    document.getElementById("viewNotePopup").style.display =
        "flex";
}

function closeNoteViewer()
{
    document.getElementById("viewNotePopup").style.display =
        "none";
}
</script>

</script>
<script>
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

        // Mark announcements as read
        fetch("mark_announcements_read.php")
        .then(response => response.text())
        .then(data => {

            // Hide badge after opening announcements
            const badge =
                document.getElementById("announcementBadge");

            if(badge)
            {
                badge.style.display = "none";
                badge.innerHTML = "0";
            }

        });
    }
}
</script>
<script>
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
</script>
<script>

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
</script>
<script>
function updateUnreadMessages() {
    fetch("get_unread_count.php")
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
</script>
<script>

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

</script>
<script>
function showLoad() {
    document.getElementById("loadingPoup").style.display = "flex";
}

function closeError() {
    document.getElementById("errorPoup").style.display = "none";
}

window.addEventListener("load", function () {

    const loading = document.getElementById("loadingPoup");
    const error = document.getElementById("errorPoup");

    if (loading) loading.style.display = "none";

    <?php if (isset($_POST['save']) && $kraal_exists === false) { ?>
        if (error) error.style.display = "flex";
    <?php } ?>

});
</script>
<script>
document.addEventListener("input", function (e) {
    if (e.target.classList.contains("numeric-only")) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
    }
});
</script>
<script>
function toggleTableView() {
    const table = document.getElementById("tableContainer");
    table.classList.toggle("expanded");
}
</script>
<script>
function toggleTableView() {
    document.getElementById("tableContainer")
            .classList.toggle("expanded");
}

function closeTableView() {
    document.getElementById("tableContainer")
            .classList.remove("expanded");
}
</script>
<script>
function toggleTableViewBirths() {
    document.getElementById("tableContainer2")
            .classList.toggle("expanded");
}

function closeTableViewBirths() {
    document.getElementById("tableContainer2")
            .classList.remove("expanded");
}
</script>
<script>
function toggleTableViewPermitIn() {
    document.getElementById("tableContainer3")
            .classList.toggle("expanded");
}

function closeTableViewPermitIn() {
    document.getElementById("tableContainer3")
            .classList.remove("expanded");
}
</script>
<script>
function toggleTableViewPermitOut() {
    document.getElementById("tableContainer4")
            .classList.toggle("expanded");
}

function closeTableViewPermitOut() {
    document.getElementById("tableContainer4")
            .classList.remove("expanded");
}
</script>
<script>
function toggleTableViewDeath() {
    document.getElementById("tableContainer5")
            .classList.toggle("expanded");
}

function closeTableViewDeath() {
    document.getElementById("tableContainer5")
            .classList.remove("expanded");
}
</script>
<script>
function toggleTableViewMaster() {
    document.getElementById("tableContainer6")
            .classList.toggle("expanded");
}

function closeTableViewMaster() {
    document.getElementById("tableContainer6")
            .classList.remove("expanded");
}
</script>
<script>
function toggleTableViewFate() {
    document.getElementById("tableContainer7")
            .classList.toggle("expanded");
}

function closeTableViewFate() {
    document.getElementById("tableContainer7")
            .classList.remove("expanded");
}
</script>
<script>
function openTransferPopup() {
    document.getElementById("transferPopup").style.display = "flex";
}
</script>
<script>
function showLoad()
{
    document.getElementById(
        "loadingPoup"
    ).style.display = "flex";
}

function hideLoad()
{
    document.getElementById(
        "loadingPoup"
    ).style.display = "none";
}
function closeErrorPopup()
{
    document.getElementById(
        "errorPopup"
    ).style.display = "none";
}

function retrieveRecord()
{
    showLoad();

    let startTime = Date.now();

    let form =
        document.getElementById(
            "dailyRecordForm"
        );

    let formData =
        new FormData(form);

    fetch("retrieve_record.php", {

        method: "POST",
        body: formData

    })

    .then(response => response.json())

    .then(data => {

        document.querySelector(
            'input[name="prev"]'
        ).value = data.om;

        document.querySelector(
            'input[name="on_register"]'
        ).value = data.or;

        document.querySelector(
            'input[name="dipped"]'
        ).value = data.dip;

        let elapsed =
            Date.now() - startTime;

        let remaining =
            Math.max(0, 2000 - elapsed);

        setTimeout(function(){

            hideLoad();

        }, remaining);

    })

   .catch(error => {

    console.error(error);

    let elapsed =
        Date.now() - startTime;

    let remaining =
        Math.max(0, 2000 - elapsed);

    setTimeout(function(){

        hideLoad();

        document.getElementById(
            "errorPopup"
        ).style.display = "flex";

    }, remaining);

});
}
</script>
<script>
function openRecordPopup()
{
    document.getElementById(
        "recordPopup"
    ).style.display = "flex";
}

function closeRecordPopup()
{
    document.getElementById(
        "recordPopup"
    ).style.display = "none";
}
</script>
<script>
function showLoad()
{
    document.getElementById(
        "loadingPoup"
    ).style.display = "flex";
}

function hideLoad()
{
    document.getElementById(
        "loadingPoup"
    ).style.display = "none";
}
function closeErrorPopup()
{
    document.getElementById(
        "errorPopup"
    ).style.display = "none";
}

function retrieveRecord()
{
    showLoad();

    let startTime = Date.now();

    let form =
        document.getElementById(
            "dailyRecordForm"
        );

    let formData =
        new FormData(form);

    fetch("retrieve_record.php", {

        method: "POST",
        body: formData

    })

    .then(response => response.json())

   .then(data => {

    console.log(data);

    let prevField =
        document.querySelector(
            'input[name="prev"]'
        );

    let registerField =
        document.querySelector(
            'input[name="on_register"]'
        );

    let dippedField =
        document.querySelector(
            'input[name="dipped"]'
        );

    console.log("prev:", prevField);
    console.log("on_register:", registerField);
    console.log("dipped:", dippedField);

    if(prevField)
    {
        prevField.value = data.om;
    }

    if(registerField)
    {
        registerField.value = data.or;
    }

    if(dippedField)
    {
        dippedField.value = data.dip;
    }

    let elapsed =
        Date.now() - startTime;

    let remaining =
        Math.max(0, 2000 - elapsed);

    setTimeout(function(){

        hideLoad();

    }, remaining);

})

   .catch(error => {

    console.error(error);

    let elapsed =
        Date.now() - startTime;

    let remaining =
        Math.max(0, 1000 - elapsed);

    setTimeout(function(){

        hideLoad();

	showRecordErrorPopup();

    }, remaining);

});
}
</script>
<script>
function showRecordErrorPopup()
{
    document.getElementById(
        "recordErrorPopup"
    ).style.display = "flex";
}

function closeRecordErrorPopup()
{
    document.getElementById(
        "recordErrorPopup"
    ).style.display = "none";
}
</script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    document.querySelectorAll(
        "#dailyRecordForm input"
    ).forEach(function(input){

        input.addEventListener(
            "focus",
            function(){

                if(
                    this.value === "0" ||
                    this.value === "00" ||
                    this.value === "000"
                )
                {
                    this.select();
                }

            }
        );

    });

});
</script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    document.querySelectorAll(
        "#dailyRecordForm input[data-default]"
    ).forEach(function(input){

        input.addEventListener("blur", function(){

            if(this.value.trim() === "")
            {
                this.value =
                    this.dataset.default;
            }

        });

    });

});
</script>

<script>

function searchDailyRecords()
{
    showLoad();

    let form =
        document.getElementById(
            "searchDailyRecordsForm"
        );

    let formData =
        new FormData(form);

    fetch(
        "search_daily_records.php",
        {
            method: "POST",
            body: formData
        }
    )

    .then(response => response.text())

.then(data => {

    console.log("Returned data:");
    console.log(data);

    document.getElementById(
        "dailyRecordsResults"
    ).style.display = "block";

    document.getElementById(
        "dailyRecordsTableContainer"
    ).innerHTML = data;

    hideLoad();
})
    .catch(error => {

    hideLoad();

    console.error(error);

    alert(
        "Failed to load records: " +
        error.message
    );
});
console.log(
    document.getElementById(
        "dailyRecordsResults"
    )
);
}

</script>
<script>

function openBirthPopup()
{
    document.getElementById(
        "birthPopup"
    ).style.display = "flex";
}

function closeBirthPopup()
{
    document.getElementById(
        "birthPopup"
    ).style.display = "none";
}

</script>
<script>

function hideSelectedRows() {

    const selectedIds = [];

    document.querySelectorAll('.row-check:checked').forEach(cb => {
        selectedIds.push(cb.dataset.id);
    });

    if (selectedIds.length === 0) {
        document.getElementById("noSelectionPopup").style.display = "flex";
        return;
    }

    fetch("hide_selected.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            ids: selectedIds
        })
    })
    .then(response => response.json())
    .then(data => {

        console.log("Server response:", data);

        if (data.success) {

            document.getElementById("hideSuccessPopup").style.display = "flex";

            selectedIds.forEach(id => {
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.remove();
                }
            });

        } else {

            alert(data.message || "Failed to hide records");

        }

    })
    .catch(error => {

        console.error(error);
        alert("An error occurred while hiding records.");

    });

}
</script>
<script>
function openHideConfirmPopup() {

    const selectedIds = document.querySelectorAll('.row-check:checked');

    if (selectedIds.length === 0) {
        openNoSelectionPopup();
        return;
    }

    activeHideTab = "cattle";

    document.getElementById("hideConfirmPopup").style.display = "flex";
}
function openBirthHideConfirmPopup() {

    const selectedIds = document.querySelectorAll('.birth-row-check:checked');

    if (selectedIds.length === 0) {
        openNoSelectionPopup();
        return;
    }

    activeHideTab = "births";

    document.getElementById("birthHideConfirmPopup").style.display = "flex";
}

function confirmBirthHideSelected() {

    closeBirthHideConfirmPopup();

    document.getElementById("loadingPoup").style.display = "flex";

    hideBirthRecords();
}

function openHideSuccessPopup() {
    document.getElementById("hideSuccessPopup").style.display = "flex";
refreshAllTables();
}

function closeHideSuccessPopup() {

    document.getElementById("hideSuccessPopup").style.display = "none";

    const tab = sessionStorage.getItem("activeHideTab");

    if (tab) {
        localStorage.setItem("activeTab", tab);
        sessionStorage.removeItem("activeHideTab");
    }

    // ❌ REMOVE PAGE RELOAD
    // location.reload();

    // ✅ Instead refresh ONLY the affected table
    if (tab === "birthTab") {
        refreshTable("fetch_births.php", "birthRecords");
    }

    if (tab === "permitInTab") {
        refreshTable("fetch_permits_in.php", "permitInRecords");
    }

    if (tab === "deathTab") {
        refreshTable("fetch_deaths.php", "deathRecords");
    }

    if (tab === "masterTab") {
        refreshTable("fetch_master_register.php", "masterRegisterRecords");
    }

}

function openNoSelectionPopup() {
    document.getElementById("noSelectionPopup").style.display = "flex";
}

function closeNoSelectionPopup() {
    document.getElementById("noSelectionPopup").style.display = "none";
}

</script>
<script>
function hideBirthRecords() {

    const selectedIds = [];

    document.querySelectorAll(".birth-row-check:checked")
        .forEach(cb => {
            selectedIds.push(cb.dataset.id);
        });

    console.log("Selected IDs:", selectedIds);

    fetch("hide_birth_records.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            ids: selectedIds
        })
    })
    .then(response => response.text())
    .then(text => {

        console.log("Server response:", text);

        const data = JSON.parse(text);

        document.getElementById("loadingPoup").style.display = "none";

        if (data.success) {

            // Remove the hidden rows immediately
            selectedIds.forEach(id => {

                const row = document.querySelector(`tr[data-id="${id}"]`);

                if (row) {
                    row.remove();
                }

            });

            // Then show the success popup
            openHideSuccessPopup();

        } else {

            alert(data.message || "Failed to hide records");

        }

    })
    .catch(error => {

        document.getElementById("loadingPoup").style.display = "none";

        console.error("Error:", error);

    });

}
function refreshBirthRecords() {

    fetch("fetch_births.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("birthRecords").innerHTML = html;
        })
        .catch(error => console.error(error));

}
function openBirthHideConfirmPopup() {

    const selectedIds = document.querySelectorAll(
        ".birth-row-check:checked"
    );

    if (selectedIds.length === 0) {

        openNoSelectionPopup();
        return;
    }

    document.getElementById(
        "birthHideConfirmPopup"
    ).style.display = "flex";
}
function openBirthHideConfirmPopup() {

    const selectedIds = document.querySelectorAll(
        ".birth-row-check:checked"
    );

    if (selectedIds.length === 0) {
        openNoSelectionPopup();
        return;
    }

    document.getElementById(
        "birthHideConfirmPopup"
    ).style.display = "flex";
}

function closeBirthHideConfirmPopup() {

    document.getElementById(
        "birthHideConfirmPopup"
    ).style.display = "none";
}

function confirmBirthHideSelected() {

    closeBirthHideConfirmPopup();

    document.getElementById(
        "loadingPoup"
    ).style.display = "flex";

    hideBirthRecords();
}
function confirmHideSelected() {

    // Save current tab so it can be restored after reload
    const activeTab = document.querySelector(".tab-content.active");

    if (activeTab) {
        sessionStorage.setItem(
            "activeHideTab",
            activeTab.id
        );
    }

    // Close confirmation popup
    closeHideConfirmPopup();

    // Show loading popup
    document.getElementById(
        "loadingPoup"
    ).style.display = "flex";

    // Hide selected records
    hideSelectedRows();
function closeHideConfirmPopup() {

    document.getElementById(
        "hideConfirmPopup"
    ).style.display = "none";

}
}
</script>
<script>
function openFatePopup() {

    document.getElementById(
        "fatePopup"
    ).style.display = "flex";

}

function closeFatePopup() {

    document.getElementById(
        "fatePopup"
    ).style.display = "none";

}
</script>
<script>
function searchFateTable() {

    let filter = document
        .getElementById("fateSearch")
        .value
        .toUpperCase();

    let rows = document.querySelectorAll(
        "#fateRecords tr"
    );

    rows.forEach(row => {

        let animalId =
            row.cells[0]?.textContent
            .toUpperCase() || "";

        if (animalId.includes(filter)) {

            row.style.display = "";

        } else {

            row.style.display = "none";
        }
    });
}
</script>
<script>

function openPermitInPopup(){

    document.getElementById(
        "permitInPopup"
    ).style.display = "flex";
}

function closePermitInPopup(){

    document.getElementById(
        "permitInPopup"
    ).style.display = "none";
}

</script>
<script>
function toggleTagMenu(){
    document.getElementById("tagMenu").classList.toggle("show");
}

/* close when clicking outside */
document.addEventListener("click", function(e){
    const dropdown = document.querySelector(".tag-dropdown");
    const menu = document.getElementById("tagMenu");

    if(!dropdown.contains(e.target)){
        menu.classList.remove("show");
    }
});
</script>
<script>
function loadEartagRequests()
{
    fetch('request_eartags.php')
    .then(response => response.text())
    .then(data =>
    {
        document.getElementById('eartagRequestBody').innerHTML = data;

        document.getElementById('requestEartagPopup').style.display = 'flex';

        // Attach Select All event
        const selectAll = document.getElementById('selectAllTags');

        if(selectAll)
        {
            selectAll.onchange = function()
            {
                document.querySelectorAll('.animal-checkbox')
                .forEach(function(box)
                {
                    box.checked = selectAll.checked;
                });
            };
        }
    })
    .catch(error => console.error('Error loading eartag requests:', error));
}

function closeRequestEartagPopup()
{
    document.getElementById('requestEartagPopup').style.display = 'none';
}
</script>
<script>
function searchEartagTable() {

    let filter = document
        .getElementById("eartagSearch")
        .value
        .toUpperCase();

    let rows = document.querySelectorAll(
        "#eartagRequestBody tr"
    );

    rows.forEach(row => {

        let text =
            row.textContent.toUpperCase();

        row.style.display =
            text.includes(filter)
            ? ""
            : "none";

    });
}
</script>
<script>

function generateTagRequest()
{
    let checkboxes =
        document.querySelectorAll(
            '.animal-checkbox'
        );

    console.log(
        "Total checkboxes:",
        checkboxes.length
    );

    let selected = [];

    checkboxes.forEach(cb =>
    {
        console.log(
            "Value:",
            cb.value,
            "Checked:",
            cb.checked
        );

        if(cb.checked)
        {
            selected.push(cb.value);
        }
    });

    console.log(
        "Selected:",
        selected
    );

    if(selected.length === 0)
    {
        alert(
            'Select at least one animal'
        );
        return;
    }

    showLoadingPopup();

    fetch('save_eartag_request.php', {

        method: 'POST',

        headers: {
            'Content-Type':
            'application/json'
        },

        body: JSON.stringify(selected)

    })
    .then(res => res.text())
    .then(msg =>
    {
        hideLoadingPopup();

        alert(msg);

        location.reload();
    })
    .catch(error =>
    {
        hideLoadingPopup();

        console.error(error);

        alert('Error saving request');
    });

} // <-- THIS WAS MISSING


function showLoadingPopup()
{
    document.getElementById(
        'loadingPoup'
    ).style.display = 'flex';
}

function hideLoadingPopup()
{
    document.getElementById(
        'loadingPoup'
    ).style.display = 'none';
}

</script>
<script>
function loadApplyEartags()
{
    fetch('load_approved_eartags.php')

    .then(response => response.text())

    .then(data =>
    {
        document.getElementById(
            'applyEartagBody'
        ).innerHTML = data;

        document.getElementById(
            'applyEartagPopup'
        ).style.display = 'flex';

        const selectAll =
            document.getElementById(
                'selectAllApplyTags'
            );

        if(selectAll)
        {
            selectAll.onchange =
            function()
            {
                document
                .querySelectorAll(
                    '.apply-checkbox'
                )
                .forEach(box =>
                {
                    box.checked =
                    selectAll.checked;
                });
            };
        }
    });
}
function closeApplyEartagPopup()
{
    document.getElementById(
        'applyEartagPopup'
    ).style.display = 'none';
}
function searchApplyEartagTable()
{
    let filter =
        document
        .getElementById(
            'applyTagSearch'
        )
        .value
        .toUpperCase();

    let rows =
        document.querySelectorAll(
            '#applyEartagBody tr'
        );

    rows.forEach(row =>
    {
        let eartag =
            row.cells[1]
            ?.textContent
            .toUpperCase() || '';

        row.style.display =
            eartag.includes(filter)
            ? ''
            : 'none';
    });
}
function applySelectedEartags()
{
    let selected = [];

    document
    .querySelectorAll(
        '.apply-checkbox:checked'
    )
    .forEach(box =>
    {
        selected.push(
            box.value
        );
    });

    if(selected.length === 0)
    {
        alert(
            'Select at least one eartag.'
        );

        return;
    }

    fetch(
        'apply_eartags.php',
        {
            method:'POST',

            headers:{
                'Content-Type':
                'application/json'
            },

            body:JSON.stringify(
                selected
            )
        }
    )

    .then(response =>
        response.text()
    )

    .then(msg =>
    {
        alert(msg);

        closeApplyEartagPopup();

        location.reload();
    });
}

</script>
<script>
document.addEventListener('DOMContentLoaded', function()
{
    const birthForm =
        document.getElementById('birthForm');

    if(!birthForm)
    {
        console.error(
            'birthForm not found'
        );
        return;
    }

    birthForm.addEventListener(
        'submit',
        function(e)
        {
            e.preventDefault();

            showLoadingPopup();

            let formData =
                new FormData(this);

            fetch('save_birth.php',
            {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data =>
            {
                hideLoadingPopup();

                data = data.trim();

                console.log(
                    "Server response:",
                    data
                );

                /* KRAAL NOT FOUND */
                if(data === 'KRAAL_NOT_FOUND')
                {
                    showErrorPopup(
                        'Kraal does not exist.'
                    );
                    return;
                }

                /* DAM NOT FOUND */
                if(data === 'DAM_NOT_FOUND')
                {
                    showErrorPopup(
                        'Dam does not exist in Master Register.'
                    );
                    return;
                }

                /* SUCCESS */
                if(data.startsWith('Success'))
                {
                    closeBirthPopup();

                    showSuccessPopup(data);

                    return;
                }

                /* OTHER ERRORS */
                showErrorPopup(data);
            })
            .catch(error =>
            {
                hideLoadingPopup();

                console.error(error);

                showErrorPopup(
                    'An error occurred while saving.'
                );
            });
        }
    );
});
</script>
<script>
function showLoadingPopup()
{
    document.getElementById('loadingPoup')
        .style.display = 'flex';
}

function hideLoadingPopup()
{
    document.getElementById('loadingPoup')
        .style.display = 'none';
}
function closeDamError()
{
    document.getElementById('damErrorPopup')
        .style.display = 'none';
}

</script>
<script>
document.addEventListener('DOMContentLoaded', function()
{
    const permitInForm = document.querySelector('#permitInForm');

    if(!permitInForm)
    {
        console.error(
            'permitInForm not found'
        );
        return;
    }

    permitInForm.addEventListener(
        'submit',
        function(e)
        {
            e.preventDefault();

            showLoadingPopup();

            let formData =
                new FormData(this);

            fetch('save_permit_in.php',
            {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data =>
            {
                hideLoadingPopup();

                data = data.trim();

                console.log(
                    'Server response:',
                    data
                );

                /* KEEP FORM OPEN */
                if(data === 'ANIMAL_EXISTS')
                {
                    showErrorPopup(
                        'Animal already exists in system'
                    );
                    return;
                }

                if(data === 'KRAAL_NOT_FOUND')
                {
                    showErrorPopup(
                        'Kraal does not exist'
                    );
                    return;
                }

                if(data === 'ANIMAL_NOT_FOUND')
                {
                    showErrorPopup(
                        'Animal does not exist in master register'
                    );
                    return;
                }

                /* CLOSE FORM ONLY ON SUCCESS */
                if(data.startsWith('Success'))
                {
                    closePermitInPopup();

                    showSuccessPopup(data);

                    return;
                }

                showErrorPopup(data);
            })
            .catch(error =>
            {
                hideLoadingPopup();

                console.error(error);

                showErrorPopup(
                    'System error occurred'
                );
            });
        }
    );
});
document.querySelector("#permitInRecords").innerHTML = "";
fetch("fetch_permits_in.php")
    .then(res => res.text())
    .then(html => {
        document.querySelector("#permitInRecords").innerHTML = html;
    });
</script>
<script>

function showErrorPopup(message)
{
    document.querySelector('#errorPoup .popup-body')
        .innerHTML =
        `<div class="popup-icon error-icon">✖</div>${message}`;

    document.getElementById('errorPoup')
        .style.display = 'flex';
}

function closeError()
{
    document.getElementById('errorPoup')
        .style.display = 'none';
}

function openPermitOutPopup()
{
    document.getElementById('permitOutPopup').style.display = 'flex';
}

function closePermitOutPopup()
{
    document.getElementById('permitOutPopup').style.display = 'none';
}

</script>
<script>
document.addEventListener('DOMContentLoaded', function()
{
    const form = document.getElementById('permitOutForm');

    console.log("Permit form found:", form);

    if(!form) return;

    form.addEventListener('submit', function(e)
    {
        e.preventDefault();

        showLoadingPopup();

        fetch('save_permit_out.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(res => res.text())
        .then(data =>
        {
            hideLoadingPopup();

            data = data.trim();

            if(data === "ANIMAL_NOT_FOUND")
                return showErrorPopup("Animal not found");

            if(data === "KRAAL_NOT_FOUND")
                return showErrorPopup("Kraal not found");

            if(data.startsWith("Success"))
            {
                closePermitOutPopup();
                showSuccessPopup(data);
            }
        })
        .catch(err =>
        {
            hideLoadingPopup();
            showErrorPopup("System error");
            console.error(err);
        });
    });
});
</script>
<script>
function openDeathPopup()
{
    document.getElementById(
        'deathPopup'
    ).style.display = 'flex';
}

function closeDeathPopup()
{
    document.getElementById(
        'deathPopup'
    ).style.display = 'none';
}
</script>
<script>
document.addEventListener(
'DOMContentLoaded',
function()
{
    const deathForm =
        document.getElementById('deathForm');

    if(!deathForm) return;

    deathForm.addEventListener(
    'submit',
    function(e)
    {
        e.preventDefault();

        showLoadingPopup();

        fetch(
            'save_death.php',
            {
                method:'POST',
                body:new FormData(this)
            }
        )
        .then(res => res.text())
        .then(data =>
        {
            hideLoadingPopup();

            data = data.trim();

            console.log(
                "Server response:",
                data
            );

            if(data ===
                'ANIMAL_NOT_FOUND')
            {
                showErrorPopup(
                    'Animal does not exist in Master Register'
                );
                return;
            }

            if(data ===
                'KRAAL_NOT_FOUND')
            {
                showErrorPopup(
                    'Kraal does not exist'
                );
                return;
            }

            if(data.startsWith(
                'Success'))
            {
                closeDeathPopup();

                showSuccessPopup(
                    data
                );

                return;
            }

            showErrorPopup(data);
        })
        .catch(error =>
        {
            hideLoadingPopup();

            console.error(error);

            showErrorPopup(
                'System error occurred'
            );
        });

    });
});
</script>
<script>

function hidePermitInRecords() {

    const selectedIds = [];

    document.querySelectorAll(".permitin-row-check:checked")
        .forEach(cb => {
            selectedIds.push(cb.dataset.id);
        });

    console.log("Selected IDs:", selectedIds);

    fetch("hide_permitin_records.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            ids: selectedIds
        })
    })

    .then(response => response.text())
    .then(text => {

        console.log("Server response:", text);

        const data = JSON.parse(text);

        document.getElementById("loadingPoup").style.display = "none";

        if (data.success) {

            // Hide rows instantly in UI
            selectedIds.forEach(id => {
                const checkbox = document.querySelector(`.permitin-row-check[data-id="${id}"]`);

                if (checkbox) {
                    const row = checkbox.closest("tr");
                    if (row) {
                        row.style.display = "none";
                    }
                }
            });

            openHideSuccessPopup();

        } else {
            alert(data.message || "Failed to hide records");
        }
    })

    .catch(error => {

        document.getElementById("loadingPoup").style.display = "none";

        console.error("Error:", error);

    });

}


function openPermitInHideConfirmPopup() {

    const selectedIds = document.querySelectorAll(".permitin-row-check:checked");

    if (selectedIds.length === 0) {
        openNoSelectionPopup();
        return;
    }

    document.getElementById("permitInHideConfirmPopup").style.display = "flex";
}


function closePermitInHideConfirmPopup() {
    document.getElementById("permitInHideConfirmPopup").style.display = "none";
}


function confirmPermitInHideSelected() {

    const activeTab = document.querySelector(".tab-content.active");

    if (activeTab) {
        sessionStorage.setItem("activeHideTab", activeTab.id);
    }

    closePermitInHideConfirmPopup();

    document.getElementById("loadingPoup").style.display = "flex";

    hidePermitInRecords();
}

</script>
<script>
function refreshAllTables() {

    // Births
    fetch("fetch_births.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("birthRecords").innerHTML = html;
        });

    // Permit In
    fetch("fetch_permits_in.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("permitInRecords").innerHTML = html;
        });

    // Deaths
    fetch("fetch_deaths.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("deathRecords").innerHTML = html;
        });

    // Master Register
    fetch("fetch_master_register.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("masterRecords").innerHTML = html;
        });

    // Permit Out
    fetch("fetch_permits_out.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("permitOutRecords").innerHTML = html;
        });
// Fate
    fetch("fetch_fate_register.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("fateRecords").innerHTML = html;
        });

}
function refreshTable(url, tbodyId) {

    fetch(url)
        .then(res => res.text())
        .then(html => {
            document.getElementById(tbodyId).innerHTML = html;
        })
        .catch(err => console.error(err));

}
function toggleSelectColumn(tabId, show) {

    // Get the tab container
    const tab = document.getElementById(tabId);

    // Safety check (prevents null errors)
    if (!tab) {
        console.error("Tab not found: " + tabId);
        return;
    }

    // Find all select columns ONLY inside this tab
    const selectCells = tab.querySelectorAll(".select-col");

    // Show or hide each cell
    selectCells.forEach(cell => {
        cell.style.display = show ? "" : "none";
    });

}
function togglePermitOutDeleteMode(){

    
    document
    .getElementById("permitoutWrapper")
    .classList.toggle("delete-mode");

    const bar = document.getElementById("permitOutDeleteBar");

    if(bar.style.display=="flex"){

        bar.style.display="none";

    }else{

        bar.style.display="flex";

    }

}
function togglePermitOutSelectAll(source){

    document.querySelectorAll(".permitout-row-check")
        .forEach(cb => cb.checked = source.checked);

}
</script>
<script>
function hidePermitOutRecords() {

    const selectedIds = [];

    document.querySelectorAll(".permitout-row-check:checked")
        .forEach(cb => {
            selectedIds.push(cb.dataset.id);
        });

    console.log("Selected IDs:", selectedIds);

    fetch("hide_permitout_records.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            ids: selectedIds
        })
    })

    .then(response => response.text())

    .then(text => {

        console.log("Server response:", text);

        const data = JSON.parse(text);

        document.getElementById("loadingPoup").style.display = "none";

        if (data.success) {

            openHideSuccessPopup();

        } else {

            alert(data.message || "Failed to hide records");

        }

    })

    .catch(error => {

        document.getElementById("loadingPoup").style.display = "none";

        console.error("Error:", error);

    });

}


function openPermitOutHideConfirmPopup() {

    const selectedIds = document.querySelectorAll(
        ".permitout-row-check:checked"
    );

    if (selectedIds.length === 0) {

        openNoSelectionPopup();
        return;

    }

    document.getElementById(
        "permitOutHideConfirmPopup"
    ).style.display = "flex";

}


function closePermitOutHideConfirmPopup() {

    document.getElementById(
        "permitOutHideConfirmPopup"
    ).style.display = "none";

}


function confirmPermitOutHideSelected() {

    const activeTab = document.querySelector(".tab-content.active");

    if (activeTab) {

        sessionStorage.setItem(
            "activeHideTab",
            activeTab.id
        );

    }

    closePermitOutHideConfirmPopup();

    document.getElementById(
        "loadingPoup"
    ).style.display = "flex";

    hidePermitOutRecords();

}
</script>
<script>
function updatePresence() {
    fetch('update_status.php', {
        method: 'POST',
        credentials: 'include'
    });
}

// send every 20 seconds
setInterval(updatePresence, 20000);

// also send immediately on page load
updatePresence();

setInterval(() => {
    fetch('presence_cleanup.php');
}, 60000);
</script>
</body>
</html>