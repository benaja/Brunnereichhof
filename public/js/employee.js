$(document).ready(function () {
	$('select').formSelect();
	$('.collapsible').collapsible();

	$(".profileimage-container").on("click", ".edit-image", function () {
		$("#profileimage-edit").click();
	})

	$(".profileimage-container").on("click", ".create-image" , function () {
		$("#profileimage-edit").click();
	})

	$(".profileimage-container").on("click", ".delete-image", function(){
		$.ajax({
			url: "/employee/" + employeeId,
			type: 'patch',
			data: {
				element: "profileimage",
				data: "",
				profileimage: "delete"
			}
		}).done(() => {
            $(".profileimage").attr('src', '');
            $(".edit-image").remove();
            $(".delete-image").remove();
            $("<div>").addClass("new-image").appendTo(".profileimage-container");
            $("<a>").addClass("waves-effect waves-light btn create-image left").html("Bild hinzufügen").appendTo(".new-image");
            $("<i>").addClass("material-icons right").html("add").appendTo(".create-image");
        });
	});

	$('#profileimage-edit').change(function () {
		if (document.getElementById("profileimage-edit").files.length == 1) {
			var file = document.getElementById("profileimage-edit").files[0];
			var data = new FormData();
			data.append("profileimage", file); 
			$.ajax({
                url: "/employee/" + employeeId + "/editimage",
				data: data,  
				type: 'post',
				processData: false,
				contentType: false,
            }).done((response) => {
                $("#profileimage").attr('src', "/profileimages/" + response);
                $(".new-image").remove();
                $(".edit-image").remove();
                $(".delete-image").remove();
                $("<a>").addClass("waves-effect waves-light btn edit-image").html("Bild ändern").appendTo(".profileimage-container");
                $("<a>").addClass("waves-effect waves-light btn delete-image").html("Bild entfernen").appendTo(".profileimage-container");
                $("<i>").addClass("material-icons right").html("edit").appendTo(".edit-image");
                $("<i>").addClass("material-icons right").html("delete").appendTo(".delete-image");
            });
		}

	});
});