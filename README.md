# turnitin-moodle

Conjunto de scripts que permiten interacciones con Turnintin para hacer tareas de tipo "bulk" que no se pueden hacer desde el frontend proporcionado por Turnitin.

Todos los sctipts se deben instalar en un carpeta denominada `cli` dentro de la carpeta de plugin de turnitin para moodle.


## 

Este script modifica la fecha de finalización de las clases que va creando turnitin. A cada clase se le asignará como fecha de finalización la fecha máxima entre las actividades asociadas a la clase y N meses desde la ejecución del script. Siempre se puede indicar por parámetros el número de meses (que por defecto es 4) y una fecha de finalización máxima.

Si queremos obtener ayuda más detallada sobre este script ejecutamos:

```
$sudo -u www-data /usr/bin/php plagiarism/turnitin/cli/change_duedates.php -h 
```

En general lo ejecutaremos de la siguiente manera:

```
\$sudo -u www-data /usr/bin/php plagiarism/turnitin/cli/change_duedates.php --max_duedate=12-05-2010
```

En este caso nos aseguramos que todas las clases de turnitin acaban antes del día 12 del mes 5 del año 2010. Por limitaciones de la API, sólo podeos cambiar la fecha de un máximo de 200 clases por lo que hay que ejecutarlo en repetidas ocaciones. Por este motivo se ha introducido el parámetro `from_duedate` con el que podemos indicar que sólo modifique las fechas de finalización de las clases cuya fecha actual de finalización sea igual o superior a la fecha indicada:

```
\$sudo -u www-data /usr/bin/php plagiarism/turnitin/cli/change_duedates.php --max_duedate=12-05-2010 --from_duedate=12-08-2010 
```

Otro parámetro útil es `-t` que muestra los cambios que realizará sin llegar a materializarlos:

```
\$sudo -u www-data /usr/bin/php plagiarism/turnitin/cli/change_duedates.php --max_duedate=12-05-2010 --from_duedate=12-08-2010 -t
```




