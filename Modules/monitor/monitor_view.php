<?php 
  global $path, $feed_settings; 
  
  $enable_mysql_all = 0;
  if (isset($feed_settings['enable_mysql_all']) && $feed_settings['enable_mysql_all']==true) $enable_mysql_all = 1;

?>
<script type="text/javascript" src="<?php echo $path; ?>Modules/monitor/monitor.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/monitor/processlist.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/input/Views/input.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/monitor/Views/process_info.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/feed/feed.js"></script>

<br>
<div id="apihelphead"><div style="float:right;"><a href="api"><?php echo _('Monitor API Help'); ?></a></div></div>
<h2>Monitores</h2>
<!-- <p>This is an alternative entry point to 'inputs' designed around providing flexible decoding of RF12b struct based data packets</p>-->
<p> Decodificação de pacotes de dados </p>
<br>

<table class="table">

<tbody id="monitors"></tbody>
</table>

<div id="myModal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close modal-exit">×</button>
    <br><h3 id="myModalLabel"><b>Monitor <span id="myModal-monitorid"></span>: <span id="myModal-variablename"></span></b> config:</h3>
  </div>

  <div class="modal-body">
  
    <p><?php echo _('Input processes are executed sequentially with the result being passed back for further processing by the next processor in the input processing list.'); ?></p>

    <div id="processlist-ui">
        <table id="process-table" class="table">

            <tr>
                <th style='width:5%;'></th>
                <th style='width:5%;'><?php echo _('Order'); ?></th>
                <th><?php echo _('Process'); ?></th>
                <th><?php echo _('Arg'); ?></th>
                <th></th>
                <th><?php echo _('Actions'); ?></th>
            </tr>

            <tbody id="variableprocesslist"></tbody>

        </table>

        <table id="process-table" class="table">
        <tr><th>Adicionar processo:</th><tr>
        <tr>
            <td>
                <div class="input-prepend input-append">
                    <select id="process-select"></select>

                    <span id="type-value">
                        <input type="text" id="value-input" style="width:125px" />
                    </span>

                    <span id="type-input">
                        <select id="input-select" style="width:140px;"></select>
                    </span>

                    <span id="type-feed">        
                        <select id="feed-select" style="width:140px;"></select>
                        
                        <input type="text" id="feed-name" style="width:150px;" placeholder="Feed name..." />

                        <span class="add-on feed-engine-label">Armazenamento: </span>
                        <select id="feed-engine">

                        <optgroup label="Recommended">
                        <option value=6 selected>Intervalo Fixo Com Média</option>
                        <option value=5 >Intervalo Fixo Sem Média</option>
                        <option value=2 >Intervalo Variável Sem Média</option>
                        </optgroup>

                        <optgroup label="Other">
                        <option value=4 >PHPTIMESTORE (Timestore em PHP)</option>  
                        <option value=1 >TIMESTORE (Necessita instalação do timestore)</option>
                        <option value=3 >GRAPHITE (Necessita instalação do graphite)</option>
                        <option value=0 >MYSQL</option>
                        </optgroup>

                        </select>


                        <select id="feed-interval" style="width:130px">
                            <option value="">Selecione intervalo</option>
                            <option value=5>5s</option>
                            <option value=10>10s</option>
                            <option value=15>15s</option>
                            <option value=20>20s</option>
                            <option value=30>30s</option>
                            <option value=60>60s</option>
                            <option value=120>2 mins</option>
                            <option value=300>5 mins</option>
                            <option value=600>10 mins</option>
                            <option value=1200>20 mins</option>
                            <option value=1800>30 mins</option>
                            <option value=3600>1 hora</option>
                        </select>
                        
                    </span>
                    <button id="process-add" class="btn btn-info"><?php echo _('Add'); ?></button>
                </div>
            </td>
        </tr>
        <tr>
          <td id="description"></td>
        </tr>
        </table>
    </div>

  
  </div>

  <div class="modal-footer">
    <button class="btn btn-primary modal-exit">Ok</button>
  </div>
</div>

<script>

  var path = "<?php echo $path; ?>";
  
  processlist_ui.enable_mysql_all = <?php echo $enable_mysql_all; ?>;
  
  var monitors = monitor.getall();
  
  var decoders = {
  
    nodecoder: {
      name: 'No decoder',
      variables:[]
    },
	
	Modbus_PM210: {
      name: 'Registadores Modbus PM-210',
      updateinterval: 30,
      variables: [	  
        /* {name: 'Real_Energy_Consumption_H', type: 1, units: 'kWh'}, */ 	/* scale = reg 4108 */
        /* {name: 'Real_Energy_Consumption_L', type: 1, units: 'kWh'},  */	/* scale = reg 4108 */
        /* {name: 'Apparent_Energy_Consumption_H', type: 1, units: 'kVAh'}, */ /* scale = reg 4108 */
        /* {name: 'Apparent_Energy_Consumption_L', type: 1, units: 'kVAh'}, */ /* scale = reg 4108 */
        /* {name: 'Reactive_Energy_Consumption_H', type: 1, units: 'kVARh'}, */ /* scale = reg 4108 */ 
        /* {name: 'Reactive_Energy_Consumption_L', type: 1, units: 'kVARh'},  */ /* scale = reg 4108 */ 
		{name: 'Real_Energy_Consumption', type: 2 , units: 'kWh'}, 	/* scale = reg 4108 */
        {name: 'Apparent_Energy_Consumption', type: 2, units: 'kVAh'}, /* scale = reg 4108 */
        {name: 'Reactive_Energy_Consumption', type: 2, units: 'kVARh'}, /* scale = reg 4108 */ 
        {name: 'Total_Real_Power', type: 1, units: 'kW'}, /* scale = reg 4107 */
        {name: 'Total_Apparent_Power', type: 1, units: 'kVA'}, /* scale = reg 4107 */
        {name: 'Total_Reactive_Power', type: 1, units: 'kVAR'}, /* scale = reg 4107 */ 
        {name: 'Total_Power_Factor', type: 1, scale: 0.0001, units: 'FP'},  /* scale 0.0001, 0 to 10000  */
		{name: 'Frequency', type: 1, scale: 0.01, units: 'Hz'},  /* Hz, scale 0.01, 4500 to 6500 */
		{name: 'Total_Real_Power_Present_Demand', type: 1, units: 'kW'}, /* scale = reg 4107 */
        {name: 'Total_Apparent_Power_Present_Demand', type: 1, units: 'kVA'}, /* scale = reg 4107 */
        {name: 'Total_Reactive_Power_Present_Demand', type: 1, units: 'kVAR'}, /* scale = reg 4107 */ 
		{name: 'Total_Real_Power_Max_Demand', type: 1, units: 'kW'}, /* scale = reg 4107 */
        {name: 'Total_Apparent_Power_Max_Demand', type: 1, units: 'kVA'}, /* scale = reg 4107 */
        {name: 'Total_Reactive_Power_Max_Demand', type: 1, units: 'kVAR'}, /* scale = reg 4107 */ 
		{name: 'Current_Instantaneous_Phase_A', type: 1, units: 'A'}, /* scale = reg 4105 */
        {name: 'Current_Instantaneous_Phase_B', type: 1, units: 'A'}, /* scale = reg 4105 */
        {name: 'Current_Instantaneous_Phase_C', type: 1, units: 'A'}, /* scale = reg 4105 */ 
		{name: 'Current_Present_Demand_Phase_A', type: 1, units: 'A'}, /* scale = reg 4105 */
        {name: 'Current_Present_Demand_Phase_B', type: 1, units: 'A'}, /* scale = reg 4105 */
        {name: 'Current_Present_Demand_Phase_C', type: 1, units: 'A'}, /* scale = reg 4105 */ 		
		{name: 'Current_Max_Demand_Phase_A', type: 1, units: 'A'}, /* scale = reg 4105 */
        {name: 'Current_Max_Demand_Phase_B', type: 1, units: 'A'}, /* scale = reg 4105 */
        {name: 'Current_Max_Demand_Phase_C', type: 1, units: 'A'}, /* scale = reg 4105 */ 		
		{name: 'Voltage_Phase_A_B', type: 1, units: 'V'}, /* scale = reg 4106 */
        {name: 'Voltage_Phase_B_C', type: 1, units: 'V'}, /* scale = reg 4106 */
        {name: 'Voltage_Phase_A_C', type: 1, units: 'V'}, /* scale = reg 4106 */ 		
		{name: 'Voltage_Phase_A_N', type: 1, units: 'V'}, /* scale = reg 4106 */
        {name: 'Voltage_Phase_B_N', type: 1, units: 'V'}, /* scale = reg 4106 */
        {name: 'Voltage_Phase_C_N', type: 1, units: 'V'} /* scale = reg 4106 */ 			
      ]
    },
	
	  Modbus_T500: {
      name: 'Modbus T500',
      updateinterval: 30,
      variables: [	  
		{name: 'Slave', type: 0, scale: 1, units: ' '}, /* id */
		{name: 'Entradas', type: 0, scale: 1, units: 'b'}, /* entrada */
		{name: 'Ano', type: 0, scale: 1, units: 'a'}, /* ano */
		{name: 'Mes', type: 0, scale: 1, units: 'm'}, /* mes */
		{name: 'Dia', type: 0, scale: 1, units: 'd'}, /* dia */
		{name: 'Horas', type: 0, scale: 1, units: 'h'}, /* hora */
		{name: 'Minutos', type: 0, scale: 1, units: 'm'}, /* minuto */
		{name: 'Segundos', type: 0, scale: 1, units: 's'}, /* segundos */
	    {name: 'Voltage_Phase_Avg', type: 6, scale: 1, units: 'V'}, /* float */
		{name: 'Current_Phase_Avg', type: 6, scale: 1, units: 'A'}, /* float */
		{name: 'Voltage_Line_Avg', type: 6, scale: 1, units: 'V'}, /* float */
		{name: 'Total_PF_Sign', type: 6, scale: 1, units: 'FP'},  /* sign */
		{name: 'Total_Real_Power', type: 6, scale: 1, units: 'W'}, /* float */
		{name: 'Total_Reactive_Power', type: 6, scale: 1, units: 'VAR'}, /* float */ 
        {name: 'Total_Apparent_Power', type: 6, scale: 1, units: 'VA'}, /* float */
        {name: 'Current_PhaseA_Angle', type: 6, scale: 1, units: 'graus'}, /* float */
        {name: 'Total_Power_Factor', type: 6, scale: 1, units: 'FP'},  /* float */
		{name: 'Total_PF_Carac', type: 4, scale: 1, units: 'FP'},  /* long  */
		{name: 'Frequency', type: 6, scale: 1, units: 'Hz'},  /* Hz, float */		
      ]
    },


  /*
    lowpowertemperaturenode: {
      name: 'Low power temperature node',
      updateinterval: 60,
      variables: [
        {name: 'Temperature', type: 1, scale: 0.01, units: '°C' },
        {name: 'Battery Voltage', type: 1, scale:0.001, units: 'V'}
      ]
    },
    
    emonTxV3_RFM12B_DiscreteSampling: {
      name: 'EmonTx V3 RFM12B DiscreteSampling',
      updateinterval: 10,
      variables: [
        {name: 'Power 1', type: 1, units: 'W'}, 
        {name: 'Power 2', type: 1, units: 'W'}, 
        {name: 'Power 3', type: 1, units: 'W'}, 
        {name: 'Power 4', type: 1, units: 'W'},
        {name: 'Vrms', type: 1, scale: 0.01, units: 'V'}, 
        {name: 'temp', type: 1, scale: 0.1, units: '°C'}
      ]
    },

    emonTxV3_continuous_whtotals: {
      name: 'EmonTx V3 (Continuous sampling with Wh totals)',
      updateinterval: 10,
      variables: [
        {name: 'Message Number', type: 2 },
        {name: 'Power CT1', type: 1, units: 'W'}, 
        {name: 'Power CT2', type: 1, units: 'W'}, 
        {name: 'Power CT3', type: 1, units: 'W'}, 
        {name: 'Power CT4', type: 1, units: 'W'},
        {name: 'Wh CT1', type: 2, units: 'Wh'}, 
        {name: 'Wh CT2', type: 2, units: 'Wh'}, 
        {name: 'Wh CT3', type: 2, units: 'Wh'}, 
        {name: 'Wh CT4', type: 2, units: 'Wh'}
      ]
    },
    
    emonTH_DHT22_DS18B20: {
      name: 'EmonTH DHT22 DS18B20',
      updateinterval: 60,
      variables: [
        {name: 'Internal temperature', type: 1, scale: 0.1, units: '°C'}, 
        {name: 'External temperature', type: 1, scale: 0.1, units: '°C'}, 
        {name: 'Humidity', type: 1, scale: 0.1, units: '%'}, 
        {name: 'Battery Voltage', type: 1, scale: 0.1, units: 'V'},
      ]
    },
	*/
    
    custom: {
      name: 'Custom decoder',
      variables:[]
    },
  };
 
 redraw();
 
 var variable_edit_mode = false;
 
 var interval = setInterval(update,5000);
 
 function update()
 {
   monitors = monitor.getall();
   redraw();
 }
 
function int32tofloat(a)
{
	var sign = (a & 0x80000000) ? -1 : 1;
    
    if(a==0) return 0;
        
    if(a == 0x7FC00000 || a == 0x7F800000)
    {
    	//return Math.sqrt(-1);
		return 0;
    }
    
	var r = (a & 0x7fffff | 0x800000) * 1.0 / Math.pow(2,23) * Math.pow(2,  ((a>>23 & 0xff) - 127));

    
	return sign*r;
}

 function redraw()
 {
    var out = "";
    for (z in monitors)
    {
      var monitorname = '(Click to select a decoder)';
      if (monitors[z].decoder!=undefined && monitors[z].decoder.name!=undefined) monitorname = monitors[z].decoder.name;
        
      out += "<tr style='background-color:#eee' monitor="+z+"><td><b>Monitor "+z+"</b></td><td><span class='select-decoder' monitor="+z+" mode='namedisplay'><b>"+monitorname+"</b></span><span monitor="+z+" class='customdecoder'></span></td><td>"+list_format_updated(monitors[z].time)+"</td><td></td></tr>";
     
      var bytes = monitors[z].data.split(',');
      var pos = 0;
      
      if (monitors[z].decoder!=undefined && monitors[z].decoder.variables.length>0)
      {
        for (i in monitors[z].decoder.variables)
        {
          var variable = monitors[z].decoder.variables[i];
          
          out += "<tr style='padding:0px' monitor="+z+" variable="+i+"><td></td><td class='variable-name'>"+variable.name+" <i class='edit-variable icon-pencil' style='display:none'></i></td>";

          if (variable.type==0)
          {
            var value = parseInt(bytes[pos]);
            pos += 1;
          }
          
          if (variable.type==1)
          {
		  // little endian
            var value = parseInt(bytes[pos+1]) + parseInt(bytes[pos])*256;
            if (value>32768) value += -65536;  
            pos += 2;
          }
          
          if (variable.type==2)
          {
            // little endian
			var value = parseInt(bytes[pos]) + parseInt(bytes[pos+1])*Math.pow(2,1*8) + parseInt(bytes[pos+2])*Math.pow(2,2*8) + parseInt(bytes[pos+3])*Math.pow(2,3*8);			
            //if (value>32768) value += -65536;  
            pos += 4;
          }
		  		
          if (variable.type==3)
          {
			// big endian
            var value = parseInt(bytes[pos+1]*256) + parseInt(bytes[pos]);
            if (value>32768) value += -65536;  
            pos += 2;
          }
		  
		  if (variable.type==4)
          {
            // big endian
			var value  = parseInt(bytes[pos])*Math.pow(2,3*8) + parseInt(bytes[pos+1])*Math.pow(2,2*8) + parseInt(bytes[pos+2])*Math.pow(2,1*8) + parseInt(bytes[pos+3]) ;
            //if (value>32768) value += -65536;  
            pos += 4;
          }
		  
          if (variable.type==5)
          {
			// little endian
            var value = parseInt(bytes[pos]) + parseInt(bytes[pos+1])*Math.pow(2,1*8) + parseInt(bytes[pos+2])*Math.pow(2,2*8) + parseInt(bytes[pos+3])*Math.pow(2,3*8);			
			value = int32tofloat(value);
            pos += 4;
          }
		  
		  if (variable.type==6)
          {
		    // big endian            
			var value  = parseInt(bytes[pos])*Math.pow(2,3*8) + parseInt(bytes[pos+1])*Math.pow(2,2*8) + parseInt(bytes[pos+2])*Math.pow(2,1*8) + parseInt(bytes[pos+3]);			
			value = int32tofloat(value);
            pos += 4;
          }
          out += "<td>";
          
          if (variable.scale!=undefined) {
            value *= parseFloat(variable.scale);
            if (variable.scale==1.0) out += value.toFixed(0);
            else if (variable.scale==0.1) out += value.toFixed(1);
            else if (variable.scale==0.01) out += value.toFixed(2);
            else if (variable.scale==0.001) out += value.toFixed(3);
            else out += value;
          } else {
            out += value;
          }
          
          if (variable.units!=undefined) {
          
          if (variable.units=='u00b0C') variable.units = "°C";
              out += " "+variable.units;
          }
          
          var labelcolor = ""; if (variable.feedid) labelcolor = 'label-info';
          
          var updateinterval = monitors[z].decoder.updateinterval;
          
          var processliststr = ""; if (variable.processlist!=undefined) processliststr = processlist_ui.drawinline(variable.processlist);
          out += "</td><td style='text-align:right'>"+processliststr+"<span class='label "+labelcolor+" record' style='cursor:pointer' >Config <i class='icon-wrench icon-white'></i></span></td></tr>";
         
        }
      }
      
      if (monitors[z].decoder==undefined || monitors[z].decoder.variables.length==0)
      {
        out += "<tr><td></td><td><i style='color:#aaa'>Dados: "+monitors[z].data+"</i>"; 
        out += "</td><td></td></tr>";
      }
      
    }
    
    if (out=="") out = "<div class='alert alert-info' style='padding:40px; text-align:center'><h3>Nenhum monitor detectado</h3><p>Para usar este módulo enviar um vetor de bytes e a id do monitor para: "+path+"/monitor/set.json?monitorid=10&data=20,20,20,20</p></div>";
    
    $("#monitors").html(out);
  }

  // Show edit
  $("#monitors").on("mouseover",'tr',function() {
    $(".icon-pencil").hide();
    if (!variable_edit_mode) $(this).find("td[class=variable-name] > i").show();
  });
  
  // Draw in line editing for a variable when the pencil icon is clicked.
  $("#monitors").on("click", ".edit-variable", function() {
    console.log("edit variable");
    
    // Fetch the monitorid and variableid from closest table row (tr)
    var monitorid = $(this).closest('tr').attr('monitor');
    var variableid = $(this).closest('tr').attr('variable');

    console.log("Monitorid: "+monitorid+" Variable: "+variableid);
    
    interval = clearInterval(interval);
    
    var currentname = monitors[monitorid].decoder.variables[variableid].name;
    var currentscale = monitors[monitorid].decoder.variables[variableid].scale;
    if (currentscale==undefined) currentscale = 1;
    
    // Inline editing html
    var out = "<div class='input-prepend input-append'>";
    out += "<span class='add-on'>Name:</span>";
    out += "<input style='width:150px' class='variable-name-edit' type='text'/ value='"+currentname+"'>";
    out += "<span class='add-on'>Datatype:</span>";
    out += "<select class='variable-datatype-selector' style='width:130px'><option value=0>Int 8 bits</option><option value=1>Int 16 bits LE </option><option value=2>Int 32 bits LE </option><option value=3>Int 16 bits BE </option><option value=4>Int 32 bits BE</option><option value=5>Float 32 bits LE</option><option value=6>Float 32 bits BE</option></select>";
    out += "<span class='add-on'>Scale:</span>";
    out += "<input class='variable-scale-edit' style='width:60px' type='text' value='"+currentscale+"' / >";
    out += "<span class='add-on'>Units:</span>";
    out += "<select class='variable-units-selector' style='width:60px;'><option value=''></option><option>W</option><option>kW</option><option>Wh</option><option>kWh</option><option>°</option><option>°C</option><option>V</option><option>mV</option><option>A</option><option>mA</option></select>";
    out += "<button class='btn save-variable'>Save</button>";
    out += "</div>";
    
    // Insert in place of variable name
    $("tr[monitor="+monitorid+"][variable="+variableid+"] td[class=variable-name]").html(out);
    
    // Its easiest to set a select input via jquery selectors
    $(".variable-datatype-selector").val(monitors[monitorid].decoder.variables[variableid].type);
    $(".variable-units-selector").val(monitors[monitorid].decoder.variables[variableid].units);
    
    // The variable edit mode flag disabled the edit icon from appearing on other variables while editing of one is in progress
    variable_edit_mode = true;
  });

  // Called when the save button is clicked on the inline variable editor
  $("#monitors").on("click",'.save-variable', function() 
  {
    variable_edit_mode = false;
    
    // Fetch the monitorid and variableid from closest table row (tr)
    var monitorid = $(this).closest('tr').attr('monitor');
    var variableid = $(this).closest('tr').attr('variable');
    
    // Fetch the edited values from the input fields & update the decoder
    monitors[monitorid].decoder.variables[variableid].name = $(".variable-name-edit").val();
    monitors[monitorid].decoder.variables[variableid].scale = $(".variable-scale-edit").val()*1;
    monitors[monitorid].decoder.variables[variableid].units = $(".variable-units-selector").val();    
    monitors[monitorid].decoder.variables[variableid].type = $(".variable-datatype-selector").val(); 
    
    // Save the decoder
    monitor.setdecoder(monitorid,monitors[monitorid].decoder);
    
    interval = setInterval(update,5000);
    // redraw, apply new decoder
    redraw();
  });
  
  
  $("#monitors").on("click",'.record', function() 
  {
    interval = clearInterval(interval);
    // Fetch the monitorid and variableid from closest table row (tr)
    var monitorid = $(this).closest('tr').attr('monitor');
    var variableid = $(this).closest('tr').attr('variable');

    $("#myModal-monitorid").html(monitorid);
    $("#myModal-variablename").html(monitors[monitorid].decoder.variables[variableid].name);
   
    processlist_ui.monitorid = monitorid;
    processlist_ui.variableid = variableid;

    processlist_ui.init();
    processlist_ui.draw();
    
  
    $("#myModal").modal('show');
    $("#myModal").attr('monitor',monitorid);
    $("#myModal").attr('variable',variableid);
    
  });
  
  $(".modal-exit").click(function() 
  {
    $("#myModal").modal('hide');
    update();
    interval = setInterval(update,5000);
  });

  
  $("#monitors").on("click",'.select-decoder', function() 
  {
    interval = clearInterval(interval);
    var monitorid = $(this).attr('monitor');
    var mode = $(this).attr('mode');
    
    var current_decoder = 'raw';
    if (monitors[monitorid].decoder!=undefined) {
      current_decoder = monitors[monitorid].decoder.decoder;
    }
    
    if (mode=='namedisplay')
    {
      var out = "";
      for (z in decoders)
      {
        var selected = ""; if (current_decoder==z) selected = "selecionado";
        out += "<option value='"+z+"' "+selected+">"+decoders[z].name+"</option>";
      }
      $(this).html("<select class='decoderselect' monitor="+monitorid+">"+out+"</select>");
    }
    
    $(this).attr('mode','selecting')
  
  });
  
  $("#monitors").on("change",'.decoderselect', function() 
  {
    var monitorid = $(this).attr('monitor');
    var decoder = $(this).val();
    
    if (decoder=='custom')
    {
      var out = " <div class='input-prepend input-append'>";
      out += "<span class='add-on'>Nome:</span>";
      out += "<input style='width:150px' class='monitor-name-edit' type='text'/ >";
      out += "<span class='add-on'>No de variáveis:</span>";
      out += "<input style='width:60px' class='monitor-varnum-edit' type='text'/ >";
      out += "<button class='btn monitor-create' class='btn'>Criar</button>";
      out += "</div>";
      $('.customdecoder[monitor='+monitorid+']').html(out);
    }
    else 
    {
      monitors[monitorid].decoder = decoders[decoder];
      monitors[monitorid].decoder.decoder = decoder;
      
      monitor.setdecoder(monitorid,monitors[monitorid].decoder);
      redraw();
      
      $(this).parent().html("<b>"+monitors[monitorid].decoder.name+"</b>");
      $(this).attr('mode','namedisplay');
      interval = setInterval(update,5000);
    }
  });
  
  
  $("#monitors").on("click",'.monitor-create', function() 
  {
    // Fetch the monitorid from closest table row (tr)
    var monitorid = $(this).closest('tr').attr('monitor');

    var monitorname = $(".monitor-name-edit").val();
    var no_of_variables = parseInt($(".monitor-varnum-edit").val()); 
       
    monitors[monitorid].decoder = {
      name: monitorname,
      updateinterval: 10,
      variables: []
    };
    
    for (var i=0; i<no_of_variables; i++)
    {
      monitors[monitorid].decoder.variables.push({name: "variable: "+(i+1), type: 1, scale: 1, units: ''});
    }
    
    monitors[monitorid].decoder.decoder = monitorname.toLowerCase().replace(/ /g, '-');
    
    monitor.setdecoder(monitorid,monitors[monitorid].decoder);
    redraw();
    
    //interval = setInterval(update,5000);
    // redraw, apply new decoder
    //redraw();
  });
  
  // Calculate and color updated time
  function list_format_updated(time)
  {
    time = time * 1000;
    var now = (new Date()).getTime();
    var update = (new Date(time)).getTime();
    var lastupdate = (now-update)/1000;

    var secs = (now-update)/1000;
    var mins = secs/60;
    var hour = secs/3600

    var updated = secs.toFixed(0)+"s atrás";
    if (secs>180) updated = mins.toFixed(0)+" mins atrás";
    if (secs>(3600*2)) updated = hour.toFixed(0)+" horas atrás";
    if (hour>24) updated = "inativo";

    var color = "rgb(255,125,20)";
    if (secs<25) color = "rgb(50,200,50)"
    else if (secs<60) color = "rgb(240,180,20)"; 

    return "<span style='color:"+color+";'>"+updated+"</span>";
  }
   
  processlist_ui.monitors = monitors;
  processlist_ui.feedlist = feed.list_assoc();
  processlist_ui.inputlist = input.list_assoc();
  processlist_ui.processlist = input.getallprocesses();
  processlist_ui.events();
 
</script>
