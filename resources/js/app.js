let currentHasLog = false;

function openDayModal(el) {
    const dateStr = el.dataset.date;
    const minutes = el.dataset.minutes;
    const km = el.dataset.km;

    const [year, month, day] = dateStr.split("-");
    const displayDate = `${day}/${month}/${year}`;

    currentHasLog = minutes !== "" && km !== "";

    document.getElementById("modalDate").value = dateStr;
    document.getElementById("modalDateLabel").textContent = displayDate;
    document.getElementById("modalMinutes").value = minutes;
    document.getElementById("modalKm").value = km;
    document.getElementById("viewMinutes").textContent = minutes;
    document.getElementById("viewKm").textContent = km;

    if (currentHasLog) {
        showViewMode();
    } else {
        showEditMode();
    }

    document.getElementById("dayModal").showModal();
}

function showViewMode() {
    document.getElementById("viewMode").classList.remove("hidden");
    document.getElementById("editMode").classList.add("hidden");
}

function showEditMode() {
    document.getElementById("viewMode").classList.add("hidden");
    document.getElementById("editMode").classList.remove("hidden");
}

function switchToEditMode() {
    showEditMode();
}

function handleCancel() {
    if (currentHasLog) {
        showViewMode();
    } else {
        document.getElementById("dayModal").close();
    }
}

document.getElementById("dayModal").addEventListener("click", function (e) {
    if (e.target === this) {
        const editModeVisible = !document
            .getElementById("editMode")
            .classList.contains("hidden");

        if (editModeVisible) {
            handleCancel();
        } else {
            document.getElementById("dayModal").close();
        }
    }
});

window.openDayModal = openDayModal;
window.switchToEditMode = switchToEditMode;
window.handleCancel = handleCancel;
