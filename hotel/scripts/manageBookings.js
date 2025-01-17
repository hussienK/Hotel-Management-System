document.getElementById('toggleAccepted').addEventListener('click', function () {
    const acceptedTable = document.getElementById('acceptedBookings');
    acceptedTable.classList.toggle('hidden');
});

document.getElementById('toggleRejected').addEventListener('click', function () {
    const rejectedTable = document.getElementById('rejectedBookings');
    rejectedTable.classList.toggle('hidden');
});
