<!DOCTYPE html>
<!-- Bu yazılım Dr. Zafer Akçalı tarafından oluşturulmuştur -->
<!-- Programmed by Zafer Akçalı, MD-->
<!-- wos2q-converter V4.7 / 16 Aug 2023, fixes corrupted/misquoted 2 strings when saving, 2022 quartiles, added month, added article types-->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Calculate Quartiles of Publications Downloaded from Web of Science</title>
<!-- // https://github.com/eligrey/FileSaver.js-->
<script src="https://cdn.jsdelivr.net/g/filesaver.js"></script> 
</head>
<body>
<?php 
set_time_limit(120);
$returnValue="";
$nofPublications=$authorCount=0;
$noQuartile="n/a";
$quartileSource = "2022";
$quartileTolerance = "2023";
$queryYear="";
$calculateForEA=$wos2Authors=$miniMod=$fullName=FALSE;
if (isset($_POST['publications'])) {
	$csvText = $_POST['publications']; 
	if($csvText!=""){
if (isset($_POST['calcForEA'])) 
	$calculateForEA=TRUE;
if (isset($_POST['wos2Authors'])) 
	$wos2Authors=TRUE;
if (isset($_POST['miniMod'])) 
	$miniMod=TRUE;
if (isset($_POST['fullName'])) 
	$fullName=TRUE;
	
$displayIf = $_POST['displayIf']; 
$db = new SQLite3("xyz-hidden-database-abc.db");
// convert csv file to 2 dimentional associative array , 2 steps
// Step 1: remove empty lines from csv text, then split lines as array
$tmp = preg_replace('/\s*($|\n)/', '\1', $csvText);
$rows = explode ("\n",$tmp);
// Step 2: convert to associative array, split items to array items
$headers = array_shift($rows);
$header = explode ("\t",$headers);
$csv    = array();
foreach($rows as $row) {
        $csv[] = array_combine($header, explode ("\t", $row));
    }
if ($miniMod)
	$returnValue="Q\t"."Method\t"."Wos number\t"."Doc type\t"."p.Year\t"."ea.Year\t"."Year\t"."Journal\t"."issn\t"."eissn";
else
	$returnValue="Q\t"."scie\t"."ssci\t"."ahci\t"."esci\t"."istp\t"."isshp\t"."bsci\t"."bhci\t"."Method\t"."Wos number\t"."Doc type\t"."Cited\t"."Auth.#\t"."p.Year\t"."ea.Year\t"."Year\t"."Month\t"."Journal\t"."Journ\t"."Book\t"."issn\t"."eissn\t"."isbn\t"."Title\t"."Doi\t"."Vol.\t"."Issue\t"."Page.S\t"."Page.E\t"."Artic.Nr\t"."Ref.style\t"."PMID\t"."Pub type\t"."Publisher\t"."Book doi\t"."wosL\t"."doiL\t"."PMIDL\t"."Authors\t"."gAuthors\t"."Book auth\t"."Book ed\t"."Abstr\t"."M.Abstr\t"."RID\t"."OID";
if ($wos2Authors)
	$returnValue=$returnValue."\t"."Addresses\t"."Correspondence\t";
$returnValue=$returnValue."\n";
for ($i=0; $i < count ($csv); $i++)	{
	$printLine=FALSE;
	$gAuthors=$csv[$i]['CA']; // group of authors
	$bookAuth=$csv[$i]['BF']; // book auyhors
	$bookEd=$csv[$i]['BE'];		// book editors
	$abstr=$csv[$i]['AB']; // abstract
	$title= $csv[$i]['TI'];
	$pYear=$csv[$i]['PY'];
	$eaDate=$csv[$i]['EA'];
	$month=$csv[$i]['PD'];	// to help users using abta or aves
	if ($eaDate) 
		$eaYear=substr ($eaDate,-4); // rightmost 4 digit is early access year
	else $eaYear ="";
	if ($pYear == null) // if publication year is empty
		$Year = $eaYear; 
	else $Year = $pYear;
	$queryYear=$Year;
	if ($calculateForEA && $eaDate ) { // user wants to calculate quartile according to early access year
		$queryYear = $eaYear; 
	}
	$journal = $csv[$i]['SO']; 
	$journ = $csv[$i]['JI']; //Journal's short name
	$book = $csv[$i]['SE'];  // Book's name
	$issn= $csv[$i]['SN'];
	$eissn= $csv[$i]['EI'];
	$isbn = $csv[$i]['BN']; // isbn for books
	$RID = $csv[$i]['RI'];
	$OID = $csv[$i]['OI'];
	$refStyle = $journ." "; //Journal's short name .......
	$volume=$csv[$i]['VL'];
	$issue=$csv[$i]['IS'];
	$pageBegin=$csv[$i]['BP'];
	$pageEnd=$csv[$i]['EP'];
	$articleNr=$csv[$i]['AR'];
	if ($pYear) { // AMA 11th referencing style https://libguides.jcu.edu.au/ama/journal-article
		$refStyle=$refStyle.$pYear.";".$volume;
		if ($issue)
			$refStyle=$refStyle."(".$issue.")";
		$refStyle=$refStyle.":";
		if ($pageBegin) 
			$refStyle=$refStyle.$pageBegin."-".$pageEnd.".";
		if ($articleNr)
			$refStyle=$refStyle.$articleNr.".";
	}
	
	if (!$miniMod) { // it takes time, skip if not necessary
		if ($fullName)
			$authors=$csv[$i]['AF']; // For archiving full names, document pool
		else $authors=$csv[$i]['AU'];
		$authorCount=substr_count($authors,";")+1;
	}
	if ($wos2Authors) {
	$addresses=$csv[$i]['C1'];
	$correspondence=$csv[$i]['RP'];
	}
	$qissn= $csv[$i]['SN'];
	$qeissn= $csv[$i]['EI'];
	$wosNumber = $csv[$i]['UT'];
	$wosLink="https://www.webofscience.com/wos/woscc/full-record/".$wosNumber;
	$doi=$csv[$i]['DI'];
	$doiLink="https://doi.org/".$doi;
	$PMID= $csv[$i]['PM'];
	$PMIDLink="https://pubmed.ncbi.nlm.nih.gov/".$PMID;	
	$citation = $csv[$i]['TC'];
	$wosIndex = $csv[$i]['WE'];
	$SCIE=$SSCI=$AHCI=$ESCI=$ISTP=$ISSHP=$BSCI=$BHCI=""; // assign default values once
	if (strpos($wosIndex, 'SCI-EXPANDED') !== false) 
			$SCIE = "SCIE";
	if (strpos($wosIndex, '(SSCI)') !== false) 
			$SSCI= "SSCI";
	if (strpos($wosIndex, 'Humanities Citation Index') !== false) 
			$AHCI = "AHCI";
	if (strpos($wosIndex, 'ESCI') !== false) 
			$ESCI = "ESCI";
	if (strpos($wosIndex, 'CPCI-S)') !== false) 
			$ISTP = "ISTP";
	if (strpos($wosIndex, 'CPCI-SSH)') !== false) 
			$ISSHP = "ISSHP";	
	if (strpos($wosIndex, 'BKCI-S)') !== false) 
			$BSCI = "BSCI";
	if (strpos($wosIndex, 'BKCI-SSH)') !== false) 
			$BHCI = "BHCI";
	$docType = $csv[$i]['DT'];
	$pubType = $csv[$i]['PT'];
	$publisher = $csv[$i]['PU'];
	$bookDoi = $csv[$i]['D2'];
	$mAbstract = $csv[$i]['MA'];
	
	if ($qissn== null) $qissn = "?";
	if ($qeissn== null) $qeissn = "?";
	if ($queryYear >= $quartileTolerance) { // current year's quartile is not exists, tolerate it 
		$queryYear = $quartileSource ;
		$method = $quartileSource;
	}
	else {
	$method = "kesin";
	}
// WOS:000699757300005 exported by wos in which issn and eissn reversed, maybe there are much more reversed issn/eissn
	$stmt = $db->prepare('select * from quartile where year= :year and (issn= :issn OR issn= :eissn OR eissn= :issn OR eissn= :eissn)');
	$stmt->bindValue(':year',(int)$queryYear,SQLITE3_INTEGER);
	$stmt->bindValue(':issn',$qissn,SQLITE3_TEXT);
	$stmt->bindValue(':eissn',$qeissn,SQLITE3_TEXT);
    $result = $stmt->execute();
	if (!$result->fetchArray()) // query returned no quartile result
		$quartile = "Q?";
	else	{ // query result is: Q1, Q2, Q3, Q4 or n/a
		$result->reset(); 
		while ($roww = $result->fetchArray()) {
			$quartile = $roww["quartile"];
				if ($quartile !== $noQuartile) { 
					break; // if it's n/a try to fetch next match to find Q1, Q2, Q3, or Q4
				}
			}
		}

if ($quartile == 'Q?' && $ESCI !== 'ESCI' && (int)$queryYear > 1996 && $qissn !=='0964-198X' ) { // find a journal, different issn / same name except  'issn=0964-198X'
	$stmt = $db->prepare('select * from quartile where year= :year and name= :journalname');
	$stmt->bindValue(':year',(int)$queryYear,SQLITE3_INTEGER);
	$stmt->bindValue(':journalname',$journal,SQLITE3_TEXT);
    $result = $stmt->execute();
	while ($roww = $result->fetchArray()) {
	$quartile = $roww["quartile"];
		if ($quartile !== $noQuartile) { 
		break; // if it's n/a try to fetch next match to find Q1, Q2, Q3, or Q4
				}
			}
		}
if ( ($qissn==$qeissn) && $qissn == "?") {
	if ((int)$pYear==1997 && $journal=='ADVERSE DRUG REACTIONS AND TOXICOLOGICAL REVIEWS') // other journal, same name, issn='0964-198X' is not q4
		$quartile = "Q4";
		}
if ($displayIf=='displayAll')
	$printLine=TRUE;
else if ($displayIf=='q123Articles' && strpos ($docType,'Article') !== false && ($quartile == 'Q1' || $quartile == 'Q2' || $quartile == 'Q3'))
		$printLine=TRUE;
else if ($displayIf=='ssahciOnly' && ($SSCI == 'SSCI' || $AHCI =='AHCI'))
		$printLine=TRUE;
else if ($displayIf=='esciOnly' && $ESCI == 'ESCI')
		$printLine=TRUE;
else if ($displayIf=='esciExclude' && $ESCI !== 'ESCI')
		$printLine=TRUE;
if ($miniMod) {
		$nofPublications++;
		$returnValue=$returnValue.$quartile."\t".$method."\t".$wosNumber."\t".$docType."\t".$pYear."\t".$eaYear."\t".$Year."\t".$journal."\t".$issn."\t".$eissn."\n";
}
else if ($printLine) {
	$nofPublications++;
	$returnValue=$returnValue.$quartile."\t".$SCIE."\t".$SSCI."\t".$AHCI."\t".$ESCI."\t".$ISTP ."\t".$ISSHP."\t".$BSCI ."\t".$BHCI ."\t".$method."\t".$wosNumber."\t".$docType."\t".$citation."\t".$authorCount."\t".$pYear."\t".$eaYear."\t".$Year."\t".$month."\t".$journal."\t".$journ."\t".$book."\t".$issn."\t".$eissn."\t".$isbn."\t".$title."\t".$doi."\t".$volume."\t".$issue."\t".$pageBegin."\t".$pageEnd."\t".$articleNr."\t".$refStyle."\t".$PMID."\t".$pubType."\t".$publisher."\t".$bookDoi."\t".$wosLink."\t".$doiLink."\t".$PMIDLink."\t".$authors."\t".$gAuthors."\t".$bookAuth."\t".$bookEd."\t".$abstr."\t".$mAbstract."\t".$RID."\t".$OID;
if ($wos2Authors)
	$returnValue=$returnValue."\t".$addresses."\t".$correspondence;
$returnValue=$returnValue."\n";	
			} // if $printLine
		} // for
	} // if
 } // if
?>
<label for="myfile">Select a directly exported Full Content "Tab delimited file" from wos:</label>
<input type="file" id="csvfile" name="Csv File">
<button id="readBtn" onclick="readFunction()">Read</button> 
<button id="saveTxtBtn" onclick="saveTxtFunction()">Save as tab delimited file</button> 
<button id="copyBtn" onclick="copyTxtFunction()">Copy to clipboard</button> <br/>
<a href="Tab delimited file.png" target="_blank"> Show me how to export from Web of Science </a>
&emsp;&emsp;&emsp;&emsp;&emsp;Calculated #of publications=<?php echo $nofPublications;?><br/>
<!----------------------------------- for php ---------------------------------------->
<form method="post" action=""> 
<textarea rows = "45" cols = "170" name = "publications" wrap="off" id="publicationsArea"><?php echo $returnValue;?></textarea>  <br/> <input type="submit" id="gonder" disabled="true" >
&emsp;Mini-Mod<input type="checkbox" name="miniMod">
&emsp;Full name<input type="checkbox" name="fullName" checked>
&emsp;Prefer ea.Year for Q<input type="checkbox" name="calcForEA">
&emsp;Include addresses <input type="checkbox" name="wos2Authors" >
&emsp;Display all<input type="radio" name="displayIf" value="displayAll" checked="checked">
Q1,2,3 articles only<input type="radio" name="displayIf" value="q123Articles">
SSCI or AHCI only<input type="radio" name="displayIf" value="ssahciOnly">
ESCI only<input type="radio" name="displayIf" value="esciOnly">
ESCI exclude<input type="radio" name="displayIf" value="esciExclude"><br/>
</form>  <!------------------------------------ for php --------------------------------->
<span style="color: red">p.Year=</span>Publication year <span style="color: red">ea.Year=</span>Early access year <span style="color: red">Auth.#=</span>Number of authors. Calculates Q values according to <span style="color: red">p.Year </span><br/>
If publication year is empty, then quartile is calculated according to early access year.   The Emerging Sources Citation Index (ESCI) was launched in late 2015. <br/>
<span style="color: red">check </span>Include addresses, <span style="color: red">un-check </span>Full name for using q2authors  <br/>
WOS# <input type="text" name="WOSnumber" size="19" maxlength="19" id="WOSnumber" onkeydown="checkWOS(this)"/> 
<button id="gotoWOS" onclick="displayWOSdocument()">Show publication</button>
<button id="gotoCitation" onclick="displayWOScitation()">Show citations</button>
<button id="deleteWOSnumber" onclick="deleteWOSnumber()">Delete WOS #</button>
<button id="queryWOS" onclick="queryWOSnumber()">Create and copy WOS query</button>
<script>
function readFunction() {
document.getElementById('publicationsArea').value=''; // clear textarea
var file = document.getElementById('csvfile').files[0]; //get filename
var reader = new FileReader(); // html5 
	reader.onload = (function(file) {
	return function(e) {
		var csvtext = this.result;	//read from csv file
		document.getElementById('publicationsArea').value=csvtext; // show in textarea
           }
		})(file);
		reader.readAsText(file);
		document.getElementById('gonder').disabled = false;
}
function saveTxtFunction() {
document.getElementById('publicationsArea').value = document.getElementById('publicationsArea').value.replace ("\"Shunt index''", "Shunt index").replace ("\"Learning curve''", "Learning curve")
var blob = new Blob([document.getElementById('publicationsArea').value],
                { type: "text/plain;charset=utf-8" });
saveAs(blob, "output.csv");
}
function copyTxtFunction() {
document.getElementById('publicationsArea').select();
document.execCommand("copy");
}
function checkWOS(ele) {
	    if(event.key === 'Enter') 
			displayWOSdocument();       
}
function displayWOSdocument() {
var	w=document.getElementById('WOSnumber').value.replace("WOS:","").replace(" ","");
	urlText = "https://www.webofscience.com/wos/woscc/full-record/"+"WOS:"+w;
	window.open(urlText,"_blank");
}
function displayWOScitation() {
var	w=document.getElementById('WOSnumber').value.replace("WOS:","").replace(" ","");
	urlText = "https://www.webofscience.com/wos/woscc/citing-summary/"+"WOS:"+w;
	window.open(urlText,"_blank");
}
function deleteWOSnumber() { 
document.getElementById('WOSnumber').value = ""; 
}
function queryWOSnumber() {
CSV = document.getElementById('publicationsArea').value;
strippedCSV = CSV.replace (/(WOS:\w\w\w\w\w\w\w\w\w\w\w\w\w\w\w)|[^]/g,'$1'); // delete all except wos numbers
strippedCSV=strippedCSV.replace(/WOS:/g, ' OR WOS:'); // put OR before all WOS:
strippedCSV= 'UT=('+strippedCSV.slice(4) + ')'; // create wos advanced query string
document.getElementById('publicationsArea').value=strippedCSV;
document.getElementById('publicationsArea').select();
document.execCommand("copy");
window.open("https://www.webofscience.com/wos/woscc/advanced-search","_blank");
}
</script>
</body>
</html>
