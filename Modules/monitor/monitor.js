
var monitor = {

  apikey: "",

  'getall':function()
  {
    var result = {};
    $.ajax({ url: path+"monitor/getall.json", dataType: 'json', async: false, success: function(data) {result = data;} });
    return result;
  },
  
  'setdecoder':function(monitorid,decoder)
  {
    var result = {};
    $.ajax({ url: path+"monitor/setdecoder.json", data: "monitorid="+monitorid+"&decoder="+JSON.stringify(decoder), async: false, success: function(data){} });
    return result;
  },
  
  'getallprocesses':function()
  {
	var result = {};
	$.ajax({ url: path+"process/list.json", async: false, dataType: 'json', success: function(data){result = data;} });
	return result;
 }
}

