window.onload = function () {
    $('.deleteMode').hide();
    document.getElementById("add-button").disabled = true;
};

$(document).ready(function () {
    $('#editMode').click(function () {
        $('.deleteMode').toggle();
    });

    $('#port').tooltip({'trigger': 'focus', 'title': 'You can leave empty if port is 80'});
});

function checkForm(elem) {
    var b = document.getElementById("add-button");
    b.disabled = elem.value === "" || elem.value.length === 0;
}