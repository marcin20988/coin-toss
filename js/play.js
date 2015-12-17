  $(document).ready(function(){ 
    var id = 7;
    remove_row = function(id)
    {
      $("#row" + id).remove();
    };
    add_row = function()
    {
      id +=1;
      row = "<tr id='row" + id + "'>";
      row += "<td onclick='remove_row(" + id + ");'>(-)</td>";
      row += "<td>Team</td>";
      row += "<td><input value='' name = 'team" + id + "'></input></td>";
      row += "<td><input value='' name = 'colour" + id + "'></input></td></tr>";
      $("#submit").before(row);
    };
  });

