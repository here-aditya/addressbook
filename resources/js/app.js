
    $(function () {
        $("#incld_1").load("views/list_address.html"); 
        $("#incld_2").load("views/add_edit_address.html");

        // Cancel Add / Edit of form & reset it
        // Called at time of cancelling current add / edit operation by clicking cancel button
        $('#incld_2').on('click', '#cancelAdd', function() {
            $('#frmAdd')[0].reset();
            $('#divAlert').removeClass().html('');
            $('#list-tab').click();
            //$('#entry-tab').text('Add Address');
        });

        $('#list-tab').click(function() {
            $('#frmAdd')[0].reset();
            $('#entry-tab').text('Add Address');
            $('input[name="saveType"]').val('create');
            fetchAddressList();
        })

        // Create / Update Address for a user
        // Called at time of Add / Edit of address by clicking save button
        $('#incld_2').on('click', '#saveAdd', function() {
            var jqxhr = $.ajax({
                                method: "POST",
                                url: "serve/process.php?action=saveaddress",
                                data: $('#frmAdd').serialize()
                            })
                            .done(function( res ) {
                                let resObj = $.parseJSON(res);
                                if(resObj.type == 'success') {
                                    $('#divAlert')
                                        .removeClass()
                                        .addClass('alert alert-success')
                                        .html('<strong>Success !</strong> ' + resObj.message)
                                        .show()
                                        .fadeOut(5000, function() {
                                            $(this).removeClass().html('');
                                        });
                                        $('#frmAdd')[0].reset();    // reset form after add
                                        $('#entry-tab').text('Add Address');
                                        $('input[name="saveType"]').val('create');
                                } else {
                                    $('#divAlert')
                                        .removeClass()
                                        .addClass('alert alert-danger')
                                        .html('<strong>Failed !</strong> ' + resObj.message)
                                        .show()
                                        .fadeOut(5000, function() {
                                            $(this).removeClass().html('');
                                        });;
                                }
                            });
        });

        fetchAddressList();
        fillCityList(3924);    // state_id = 3924 , California
    });

    // Fetch list of cities & fill city dropdown list
    // Called at time of Page Load for first time
    function fillCityList(stateId = 0)
    {
        let jqxhr = $.ajax({
                                method: "GET",
                                url: "serve/process.php?action=listcity",
                                data: { stateId : stateId }
                            })
                            .done(function( res ) {
                                let resObj = $.parseJSON(res);
                                $('select[name="city"]').empty();
                                $('select[name="city"]').append('<option>Select</option>');
                                $.each( resObj.data, function( id, cityName ) {
                                    let newOption = '<option value="' + id + '">' + cityName + '</option>';
                                    $('select[name="city"]').append(newOption); 
                                });
                            });
    }

    // User Address Edit Operation, show edit form with prefilled data
    // Called by Clicking Edit Link against a address
    function editAddr(userId = 0)
    {
        let jqxhr = $.ajax({
                                method: "GET",
                                url: "serve/process.php?action=fetchuseraddr",
                                data: { userId : userId }
                            })
                            .done(function( res ) {
                                let resObj = $.parseJSON(res);
                                if(resObj.type == 'success') {
                                    $.each( resObj.data, function( key, rowObj ) {
                                        $('input[name="firstName"]').val(rowObj.first_name);
                                        $('input[name="lastName"]').val(rowObj.last_name);
                                        $('input[name="email"]').val(rowObj.email);
                                        $('input[name="street"]').val(rowObj.street);
                                        $('input[name="zip"]').val(rowObj.zip);
                                        $('select[name="city"]').val(rowObj.city_id); 
                                    });

                                    $('input[name="saveType"]').val('update');
                                    $('input[name="userId"]').val(userId);
                                    $('#entry-tab').text('Edit Address').click();
                                }
                            });
    }

    // Fetch all addressess
    // Called at the time of page load, Every Add / Edit Operation
    function fetchAddressList()
    {
        $('.spinner-border').removeClass('d-none');
        let jqxhr = $.ajax({
                                method: "GET",
                                url: "serve/process.php?action=listaddress"
                            })
                            .done(function( res ) {
                                $("#tblListAddr tbody").html('');
                                let resObj = $.parseJSON(res);
                                if(resObj.type == 'error') {
                                    let newRowContent = '<tr>';
                                    newRowContent += '<td colspan="7">'+resObj.message +'</td>';
                                    newRowContent = '</tr>';
                                    $("#tblListAddr tbody").append(newRowContent);
                                } else {
                                    $.each( resObj.data, function( key, rowObj ) {
                                        let newRowContent = '<tr>';
                                        newRowContent += '<td>' + rowObj.first_name + '</td>';
                                        newRowContent += '<td>' + rowObj.last_name + '</td>';
                                        newRowContent += '<td>' + rowObj.email + '</td>';
                                        newRowContent += '<td>' + rowObj.street + '</td>';
                                        newRowContent += '<td>' + rowObj.city_name + '</td>';
                                        newRowContent += '<td>' + rowObj.zip + '</td>';
                                        newRowContent += '<td><a href="javascript:editAddr('+rowObj.id+')">Edit</a></td>';
                                        newRowContent += '</tr>';
                                        $("#tblListAddr tbody").append(newRowContent);
                                    });
                                }

                                $('.spinner-border').addClass('d-none');
                            });
    }