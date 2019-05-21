#!/bin/bash


URL="http://biblioteca.ieslasenia.org/circ.php?categ=retour_xarxa&id_empr="
PDF="http://biblioteca.ieslasenia.org/pdf.php?pdfdoc=liste_pret&id_empr="
PREST="http://biblioteca.ieslasenia.org/circ.php?categ=pret&form_cb="


cat << EOF 
<html>
<head>

	<title> IES La Senia </title>
	<meta charset="UTF-8">

	<style>

		.listado {
			padding: 0px 50px;		
		}
		.grupo {
			border:solid 1px grey;
			margin: 20px;
			padding: 10px;
		}

		a {
    			text-decoration: none;
		}

	</style>

</head>
<body>

<h1> $1 </h1>

<div class="listado">

EOF

for grupo in 4ESOA 4ESOB 4ESOC 3ESOA 3ESOB 3ESOC 2ESOA 2ESOB 2ESOC 2ESOD 1ESOA 1ESOB 1ESOC 1ESOD; do 

	echo "<div class=\"grupo\">"
	echo "<h2> GRUPO: $grupo </h2>"
	echo "<table>"
	echo "<th><tr><td> Nombre </td><td> Listado en PDF </td><td> Detalles del alumn@ </td></tr></th>"
	for alumno in $(cat todos.csv|grep $grupo); do 

		f=$(echo "$alumno"|cut -d "," -f1)
		#echo " * $f"
		NOMBRE="$(echo $alumno| cut -d "," -f2)"
		APELLIDO="$(echo $alumno| cut -d "," -f 3)"
		APELLIDO_2="$(echo $alumno| cut -d "," -f 4)"
		#echo " Select id_empr from pmb.empr where empr_cb ='$f';"

		IDEMPR=$(echo "select id_empr from pmb.empr where empr_cb ='$f';"| mysql -uroot -pPASS1  2>/dev/null | grep -v id_empr)
	
		echo "<tr>"
		echo " <td> <a href=$URL$IDEMPR>$NOMBRE $APELLIDO $APELLIDO_2</a> </td><td> <a href="$PDF$IDEMPR" > Informe PDF </a></td><td><a href="$PREST$f" > Detalles </a></td> "
		# $sql = "SELECT id_empr FROM `empr` WHERE `empr_cb` = \'10308247\'";
 
		echo "</tr>"
	done

	echo "</table>"
	echo "</div>"

done

echo "</div>"
echo "</body>"
echo "</html>"
