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
function toggleDeleteMode() {
    document.body.classList.toggle("delete-mode");

    const bar = document.getElementById("deleteBar");
    bar.classList.toggle("show");
}

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
function toggleBirthDeleteMode() {

    document
    .getElementById("birthWrapper")
    .classList.toggle("delete-mode");
    const bar = document.getElementById("birthDeleteBar");

    if (bar.style.display === "flex") {
        bar.style.display = "none";
    } else {
        bar.style.display = "flex";
    }
}
function toggleBirthSelectAll(source) {

    document.querySelectorAll(".birth-row-check")
        .forEach(cb => cb.checked = source.checked);
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
function togglePermitInDeleteMode() {

    document
    .getElementById("permitinWrapper")
    .classList.toggle("delete-mode");

    const bar = document.getElementById("permitInDeleteBar");

    if (!bar) return;

    if (bar.style.display === "flex") {
        bar.style.display = "none";
    } else {
        bar.style.display = "flex";
    }
}
function togglePermitInSelectAll(source){

    document
        .querySelectorAll(".permitin-row-check")
        .forEach(cb=>cb.checked=source.checked);

}
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