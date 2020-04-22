$(document)
    .on('click', '#Primary_Sidebar-Service_Details_Overview-Information', function (e) {
        window.location.href = $(this).attr('href');
    });