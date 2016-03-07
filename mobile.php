<html><head><title>IGSIMON_MOBILE</title></head><body>
<!-- CSS -->
<style type="text/css">
table.tabelas {
  border-collapse: collapse; 
  }
table.tabelas td, 
table.tabelas th { 
  border: 1px solid black;
  padding: 5px; 
  }
</style><center><img src="logo.svg" alt="Romagnole" width=100%></center><br>
<?php
if (isset($_GET['apikey']))
{
  echo '<a href="javascript:window.location.href=window.location.href">Atualizar</a><br>';
  $apikey = $_GET['apikey'];
  $Comando = "http://$_SERVER[HTTP_HOST]/feed/list.json?apikey=$apikey";
  
  $Json=file_get_contents($Comando);
  
  $Dados = json_decode($Json);
  echo "<div align=\"right\"><b>Hor&aacute;rio: ".date('d-M-Y H:i:s T',$Dados[0]->time)."</b></div>";
  echo '<table class="tabelas" width=100%><tr><td><b>Vari&aacute;vel</b></td><td><b>Valor</b></td><td><b>Gr&aacute;fico</b></td></tr>';
  //echo "<tr><td>Hor&aacute;rio</td><td>".date('d-M-Y H:i:s T',$Dados[0]->time)."</td><td></td></tr>";
  $traducao = array(
    "fEP"=>"Energia Total (kWh)",
    "fES"=>"Energia Aparente (kVAh)",
    "fEQ"=>"Energia Reativa (kVArh)",
    "fP"=>"Pot&ecirc;ncia Ativa (kW)",
    "fS"=>"Pot&ecirc;ncia Aparente (kVA)",
    "fQ"=>"Pot&ecirc;ncia Reativa (kVAr)",
    "fPF"=>"Fator de Pot&ecirc;ncia",
    "fFreq"=>"Frequ&ecirc;ncia",
    "fPM"=>"Demanda M&eacute;dia de Pot&ecirc;ncia Ativa (kW)",
    "fSM"=>"Demanda M&eacute;dia de Pot&ecirc;ncia Aparente (kVA)",
    "fQM"=>"Demanda M&eacute;dia de Pot&ecirc;ncia Reativa (kVAr)",
    "fPP"=>"Demanda M&aacute;xima de Pot&ecirc;ncia Ativa (kW)",
    "fPS"=>"Demanda M&aacute;xima de Pot&ecirc;ncia Aparente (kVA)",
    "fPQ"=>"Demanda M&aacute;xima de Pot&ecirc;ncia Reativa (kVAr)",
    "fIa"=>"Corrente na Fase A (A)",
    "fIb"=>"Corrente na Fase B (A)",
    "fIc"=>"Corrente na Fase C (A)",
    "fIMa"=>"Demanda M&eacute;dia de Corrente na Fase A",
    "fIMb"=>"Demanda M&eacute;dia de Corrente na Fase B",
    "fIMc"=>"Demanda M&eacute;dia de Corrente na Fase C",
    "fIPa"=>"Demanda M&aacute;xima de Corrente na Fase A",
    "fIPb"=>"Demanda M&aacute;xima de Corrente na Fase B",
    "fIPc"=>"Demanda M&aacute;xima de Corrente na Fase C",
    "fVab"=>"Tens&atildeo Vab",
    "fVbc"=>"Tens&atildeo Vbc",
    "fVca"=>"Tens&atildeo Vca",
    "fVan"=>"Tens&atildeo Van",
    "fVbn"=>"Tens&atildeo Vbn",
    "fVcn"=>"Tens&atildeo Vcn",
    "fTO"=>"Temperatura do &Oacute;leo (&deg;C)",
    "fTE"=>"Temperatura dos Enrolamentos (&deg;C)",
    "fVL"=>"V&aacute;lvula de Al&iacute;vio de Press&atildeo",
    "fNO"=>"N&iacute;vel do &Oacute;leo"
  );
  foreach($Dados as $linha)
  {
    //echo $linha->name;
    //echo $linha->value;
    //realtime: http://localhost/vis/realtime?feedid=1&embed=1&apikey=1234
    if (isset($traducao[$linha->name]))
    {
      $linha->name=$traducao[$linha->name]." (".$linha->name.")";
    }
    echo "<tr><td>$linha->name</td><td>$linha->value</td><td><center><a href=\"http://$_SERVER[HTTP_HOST]/vis/realtime?feedid=".$linha->id."&embed=1&apikey=$apikey\"><img src=\"gr.svg\" alt=\"Ver\"></center></td></tr>";
  }
  
  echo '</table>';
}
else
{
    echo '<center><form action="'.$_SERVER['PHP_SELF'].'" method="get">API_Key:<br><input type="text" name="apikey"><br><input type="submit" value="Acessar"></form></center>';
}
?></body></html>