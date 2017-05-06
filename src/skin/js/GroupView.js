$(document).ready(function () {

  $.ajax({
    method: "GET",
    url: window.CRM.root + "/api/groups/" + window.CRM.currentGroup + "/roles",
    dataType: "json"
  }).done(function (data) {
    window.CRM.groupRoles = data.ListOptions;
    $("#newRoleSelection").select2({
      data: $(window.CRM.groupRoles).map(function () {
        return {
          id: this.OptionId,
          text: this.OptionName
        };
      })
    });
    initDataTable();
    //echo '<option value="' . $role['lst_OptionID'] . '">' . $role['lst_OptionName'] . '</option>';
  });

  $(".personSearch").select2({
    minimumInputLength: 2,
    ajax: {
      url: function (params) {
        return window.CRM.root + "/api/persons/search/" + params.term;
      },
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return {
          q: params.term, // search term
          page: params.page
        };
      },
      processResults: function (rdata, page) {
        var idKey = 1;
        var results = new Array();
        data = JSON.parse(rdata);
        $.each(data[0].persons, function (index, cvalue) {
          var childObject = {
            id: idKey,
            objid: cvalue.id,
            text: cvalue.displayName,
            uri: cvalue.uri
          };
          idKey++;
          results.push(childObject);
        });
        return {results: results};
      },
      cache: true
    }
  });

  $(".personSearch").on("select2:select", function (e) {
    $.ajax({
      method: "POST",
      url: window.CRM.root + "/api/groups/" + window.CRM.currentGroup + "/adduser/" + e.params.data.objid,
      dataType: "json"
    }).done(function (data) {
      var person = data.Person2group2roleP2g2rs[0];
      var node = window.CRM.DataTableAPI.row.add(person).node();
      window.CRM.DataTableAPI.rows().invalidate().draw(true);
      $(".personSearch").val(null).trigger('change')
    });
  });

  $("#deleteSelectedRows").click(function () {
    var deletedRows = window.CRM.DataTableAPI.rows('.selected').data()
    bootbox.confirm({
      message: "Are you sure you want to remove the " + deletedRows.length + " selected group members?",
      buttons: {
        confirm: {
          label: 'Yes',
            className: 'btn-success'
        },
        cancel: {
          label: 'No',
          className: 'btn-danger'
        }
      },
      callback: function (result)
      {
        if (result)
        {
          $.each(deletedRows, function (index, value) {
            $.ajax({
              type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
              url: window.CRM.root + '/api/groups/' + window.CRM.currentGroup + '/removeuser/' + value.PersonId, // the url where we want to POST
              dataType: 'json', // what type of data do we expect back from the server
              data: {"_METHOD": "DELETE"},
              encode: true
            }).done(function (data) {
              window.CRM.DataTableAPI.row(function (idx, data, node) {
                if (data.PersonId == value.PersonId) {
                  return true;
                }
              }).remove();
              window.CRM.DataTableAPI.rows().invalidate().draw(true);
            });
          });
        }
       }
    });

  });

  $("#addSelectedToCart").click(function () {
    if (window.CRM.DataTableAPI.rows('.selected').length > 0)
    {
      var selectedPersons = $.map(window.CRM.DataTableAPI.rows('.selected').data(), function(val,i){
        return val.PersonId;
      });
      
      window.CRM.cart.addPerson(selectedPersons);
    }

  });

  //copy membership
  $("#addSelectedToGroup").click(function () {
    $("#selectTargetGroupModal").modal("show");
    $("#targetGroupAction").val("copy");

  });

  $("#moveSelectedToGroup").click(function () {
    $("#selectTargetGroupModal").modal("show");
    $("#targetGroupAction").val("move");

  });


  $("#confirmTargetGroup").click(function () {
    var selectedRows = window.CRM.DataTableAPI.rows('.selected').data()
    var targetGroupId = $("#targetGroupSelection option:selected").val()
    var action = $("#targetGroupAction").val();

    $.each(selectedRows, function (index, value) {
      $.ajax({
        type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
        url: window.CRM.root + '/api/groups/' + targetGroupId + '/adduser/' + value.PersonId,
        dataType: 'json', // what type of data do we expect back from the server
        encode: true
      });
      if (action == "move") {
        $.ajax({
          type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
          url: window.CRM.root + '/api/groups/' + window.CRM.currentGroup + '/removeuser/' + value.PersonId,
          dataType: 'json', // what type of data do we expect back from the server
          encode: true,
          data: {"_METHOD": "DELETE"},
        }).done(function (data) {
          window.CRM.DataTableAPI.row(function (idx, data, node) {
            if (data.PersonId == value.PersonId) {
              return true;
            }
          }).remove();
          window.CRM.DataTableAPI.rows().invalidate().draw(true);
        });
      }
    });
    $(document).ajaxStop(function () {
      $("#selectTargetGroupModal").modal("hide");
    });
  });

  $("#AddGroupMembersToCart").click(function() {
    window.CRM.cart.addGroup($(this).data("groupid"));
  })

  $(document).on("click", ".changeMembership", function (e) {
    var userid = $(e.currentTarget).data("personid");
    $("#changingMemberID").val(window.CRM.DataTableAPI.row(function (idx, data, node) {
      if (data.PersonId == userid) {
        return true;
      }
    }).data().PersonId);
    $("#changingMemberName").text(window.CRM.DataTableAPI.row(function (idx, data, node) {
      if (data.PersonId == userid) {
        return true;
      }
    }).data().firstName);
    $('#changeMembership').modal('show');
    e.stopPropagation();
  });

  $(document).on("click", "#confirmMembershipChange", function (e) {
    var changingMemberID = $("#changingMemberID").val();
    $.ajax({
      method: "POST",
      url: window.CRM.root + "/api/groups/" + window.CRM.currentGroup + "/userRole/" + changingMemberID,
      data: JSON.stringify({'roleID': $("#newRoleSelection option:selected").val()}),
      dataType: "json",
      contentType: "application/json; charset=utf-8",
    }).done(function (data) {
      window.CRM.DataTableAPI.row(function (idx, data, node) {
        if (data.PersonId == changingMemberID) {
          data.RoleId = $("#newRoleSelection option:selected").val();
          return true;
        }
      }).data();
      window.CRM.DataTableAPI.rows().invalidate().draw(true);
      $('#changeMembership').modal('hide');
    });
  });

});

function initDataTable() {
  window.CRM.DataTableAPI = $("#membersTable").DataTable({
    "language": {
      "url": window.CRM.root + "/skin/locale/dataTables/" + window.CRM.locale + ".json"
    },
    "dom": 'T<"clear">lfrtip',
    "tableTools": {
      "sSwfPath": "//cdn.datatables.net/tabletools/2.2.3/swf/copy_csv_xls_pdf.swf",
      "sRowSelect": "multi",
      "aButtons": [
      {
        "sExtends": "csv",
        "bSelectedOnly": true
      }]
    },
    responsive: true,
    ajax: {
      url: window.CRM.root + "/api/groups/" + window.CRM.currentGroup + "/members",
      dataSrc: "Person2group2roleP2g2rs"
    },
    columns: [
      {
        width: 'auto',
        title: 'Name',
        data: 'PersonId',
        render: function (data, type, full, meta) {
          return '<img data-name="'+full.Person.FirstName + ' ' + full.Person.LastName + '" data-src="' + window.CRM.root + '/api/persons/' + full.PersonId + '/thumbnail" class="direct-chat-img initials-image"> &nbsp <a href="PersonView.php?PersonID="' + full.PersonId + '"><a target="_top" href="PersonView.php?PersonID=' + full.PersonId + '">' + full.Person.FirstName + " " + full.Person.LastName + '</a>';
        }
      },
      {
        width: 'auto',
        title: 'Group Role',
        data: 'RoleId',
        render: function (data, type, full, meta) {
          thisRole = $(window.CRM.groupRoles).filter(function (index, item) {
            return item.OptionId == data
          })[0];
          return thisRole.OptionName + '<button class="changeMembership" data-personid=' + full.PersonId + '><i class="fa fa-pencil"></i></button>';
        }
      },
      {
        width: 'auto',
        title: 'Address',
        render: function (data, type, full, meta) {
          return full.Person.Address1 + " " + full.Person.Address2;
        }
      },
      {
        width: 'auto',
        title: 'City',
        data: 'Person.City'
      },
      {
        width: 'auto',
        title: 'State',
        data: 'Person.State'
      },
      {
        width: 'auto',
        title: 'ZIP',
        data: 'Person.Zip'
      },
      {
        width: 'auto',
        title: 'Cell Phone',
        data: 'Person.CellPhone'
      },
      {
        width: 'auto',
        title: 'E-mail',
        data: 'Person.Email'
      }
    ],
    "fnDrawCallback": function (oSettings) {
      $("#iTotalMembers").text(oSettings.aoData.length);
      $("#membersTable .initials-image").initial();
    },
    "createdRow": function (row, data, index) {
      $(row).addClass("groupRow");
    }
  });

    $('#isGroupActive').change(function() {
        $.ajax({
            type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
            url: window.CRM.root + '/api/groups/' + window.CRM.currentGroup + '/settings/active/' + $(this).prop('checked'),
            dataType: 'json', // what type of data do we expect back from the server
            encode: true
        });
    });

    $('#isGroupEmailExport').change(function() {
        $.ajax({
            type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
            url: window.CRM.root + '/api/groups/' + window.CRM.currentGroup + '/settings/email/export/' + $(this).prop('checked'),
            dataType: 'json', // what type of data do we expect back from the server
            encode: true
        });
    });

  $(document).on('click', '.groupRow', function () {
    var selectedRows = window.CRM.DataTableAPI.rows('.selected').data().length;
    $("#deleteSelectedRows").prop('disabled', !(selectedRows));
    $("#deleteSelectedRows").text("Remove (" + selectedRows + ") Members from group");
    $("#buttonDropdown").prop('disabled', !(selectedRows));
    $("#addSelectedToGroup").prop('disabled', !(selectedRows));
    $("#addSelectedToGroup").html("Add  (" + selectedRows + ") Members to another group");
    $("#addSelectedToCart").prop('disabled', !(selectedRows));
    $("#addSelectedToCart").html("Add  (" + selectedRows + ") Members to cart");
    $("#moveSelectedToGroup").prop('disabled', !(selectedRows));
    $("#moveSelectedToGroup").html("Move  (" + selectedRows + ") Members to another group");
  });

}
