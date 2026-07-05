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

function loadBirthRecords() {
    fetch("fetch_births.php")
        .then(res => res.text())
        .then(data => {
            document.getElementById("birthRecords").innerHTML = data;
        });
}
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
   HELPERS
========================================================= */

function show(el) {
    if (el) el.classList.add("active");
}

function hide(el) {
    if (el) el.classList.remove("active");
}

/* =========================================================
   PANEL STATE
========================================================= */

function openNewMessagePanel() {
    const panel = get("newMessagePanel");
    show(panel);

    if (typeof savePanelState === "function") {
        savePanelState();
    }
}

function closeNewMessage() {
    const panel = get("newMessagePanel");
    const chatPanel = get("chatPanel");

    hide(panel);

    if (chatPanel && chatPanel.classList.contains("active")) {
        chatPanel.style.right = "380px";
    }

    if (typeof savePanelState === "function") {
        savePanelState();
    }
}

/* =========================================================
   CHAT STATE
========================================================= */

let activeUser = 0;

/* =========================================================
   LOAD MESSAGES
========================================================= */

function loadMessages(userId) {

    const chatBody = get("chatBody");
    if (!chatBody) return;

    fetch("get_messages.php?user_id=" + userId)
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
        .catch(err => console.error("Message load error:", err));
}

/* =========================================================
   OPEN CONVERSATION
========================================================= */

function openConversation(userId, userName) {

    activeUser = userId;

    fetch("mark_messages_read.php?user_id=" + userId)
        .then(r => r.text())
        .then(() => {

            if (typeof updateUnreadBadge === "function") updateUnreadBadge();
            if (typeof refreshConversations === "function") refreshConversations();

        });

    const chatPanel = get("chatPanel");
    const newMessagePanel = get("newMessagePanel");
    const chatUserName = get("chatUserName");

    if (chatUserName) {
        chatUserName.innerText = userName;
    }

    loadMessages(userId);

    /* SAFE POSITIONING */
    if (chatPanel) {

        const newMsgOpen =
            newMessagePanel && newMessagePanel.classList.contains("active");

        chatPanel.style.right = newMsgOpen ? "720px" : "380px";
        show(chatPanel);

    }

    if (typeof savePanelState === "function") {
        savePanelState();
    }
}

/* =========================================================
   CLOSE CHAT
========================================================= */

function closeChatPanel() {
    hide(get("chatPanel"));

    if (typeof savePanelState === "function") {
        savePanelState();
    }
}

/* =========================================================
   SEND MESSAGE
========================================================= */

function sendMessage() {

    const input = get("messageText");
    if (!input) return;

    const text = input.value.trim();
    if (!text || activeUser === 0) return;

    const formData = new FormData();
    formData.append("receiver_id", activeUser);
    formData.append("message", text);

    fetch("send_message.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.text())
    .then(() => {

        input.value = "";

        loadMessages(activeUser);

    })
    .catch(err => console.error("Send message error:", err));
}

/* =========================================================
   AUTO REFRESH (SAFE)
========================================================= */

setInterval(() => {

    if (activeUser > 0) {
        loadMessages(activeUser);
    }

}, 2000);

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
   PANEL STATE
========================================================= */

function openNewMessagePanel() {

    const panel = get("newMessagePanel");
    show(panel);

    if (typeof savePanelState === "function") {
        savePanelState();
    }
}

function closeNewMessage() {

    const panel = get("newMessagePanel");
    const chatPanel = get("chatPanel");

    hide(panel);

    if (chatPanel) {
        chatPanel.style.right = "380px";
    }

    if (typeof savePanelState === "function") {
        savePanelState();
    }
}

/* =========================================================
   CHAT STATE
========================================================= */

let activeUser = 0;

/* Prevent overlapping requests */
let messageController = null;

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
   OPEN CONVERSATION
========================================================= */

function openConversation(userId, userName) {

    activeUser = userId;

    fetch("mark_messages_read.php?user_id=" + userId)
        .then(r => r.text())
        .then(() => {

            if (typeof updateUnreadBadge === "function") updateUnreadBadge();
            if (typeof refreshConversations === "function") refreshConversations();

        });

    const chatPanel = get("chatPanel");
    const newMessagePanel = get("newMessagePanel");
    const chatUserName = get("chatUserName");

    if (chatUserName) {
        chatUserName.innerText = userName;
    }

    loadMessages(userId);

    if (chatPanel) {

        const isNewMessageOpen =
            newMessagePanel?.classList.contains("active");

        /* safer fallback layout */
        chatPanel.style.right = isNewMessageOpen ? "720px" : "380px";

        show(chatPanel);
    }

    if (typeof savePanelState === "function") {
        savePanelState();
    }
}

/* =========================================================
   CLOSE CHAT
========================================================= */

function closeChatPanel() {
    hide(get("chatPanel"));

    if (typeof savePanelState === "function") {
        savePanelState();
    }
}

/* =========================================================
   SEND MESSAGE
========================================================= */

function sendMessage() {

    const input = get("messageText");
    if (!input) return;

    const text = input.value.trim();
    if (!text || activeUser === 0) return;

    const formData = new FormData();
    formData.append("receiver_id", activeUser);
    formData.append("message", text);

    fetch("send_message.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.text())
    .then(() => {

        input.value = "";
        loadMessages(activeUser);

    })
    .catch(err => console.error("Send message error:", err));
}

/* =========================================================
   AUTO REFRESH (SAFE + NON-BLOCKING)
========================================================= */

setInterval(() => {

    if (activeUser > 0) {
        loadMessages(activeUser);
    }

}, 2000);

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

/* =========================================================
   GENERIC DELETE MODE SYSTEM
========================================================= */

function toggleDeleteMode(barId, tableId) {

    const bar = get(barId);
    const container = get(tableId);

    if (!bar || !container) return;

    const selectCols = container.querySelectorAll(".select-col");

    const isActive = bar.classList.contains("show");

    if (isActive) {

        bar.classList.remove("show");
        container.classList.remove("delete-mode");

        selectCols.forEach(col => col.style.display = "none");

        const selectAll = get("selectAll");
        if (selectAll) selectAll.checked = false;

        container.querySelectorAll(".row-check")
            .forEach(cb => cb.checked = false);

    } else {

        bar.classList.add("show");
        container.classList.add("delete-mode");

        selectCols.forEach(col => col.style.display = "table-cell");
    }
}

/* WRAPPERS */
function toggleMainDeleteMode() {
    toggleDeleteMode("deleteBar", "tableContainer");
}

function toggleBirthDeleteMode() {
    toggleDeleteMode("birthDeleteBar", "tableContainer2");
}

/* =========================================================
   SELECT ALL
========================================================= */

function toggleSelectAll(source) {
    document.querySelectorAll(".row-check")
        .forEach(cb => cb.checked = source.checked);
}

function toggleBirthSelectAll(source) {
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

function loadPermitInRecords(){

    refreshTable(
        "fetch_permits_in.php",
        "permitInRecords"
    );

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
