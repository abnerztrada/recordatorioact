<?php
namespace tool_recordatorio\models;

// require(dirname(dirname(__FILE__)).'/config.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/moodlelib.php');

/**
 *
 */
class correo
{

  public function __construct()
  {
    // code...
  }

  public function correo_envio(){
    //Query del curso con fechas
      $query = " Select  @s:=@s + 1 id_au, c.id as id, c.shortname, c.fullname, DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.startdate, '%Y-%m-%d '), INTERVAL -5 HOUR),'%d/%m/%Y ') AS fechainicio,
      DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.enddate, '%Y-%m-%d'), INTERVAL -5 HOUR),'%d/%m/%Y') AS fechafinal,
      DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(cd.value, '%Y-%m-%d %H:%i'), INTERVAL -5 HOUR),'%d/%m/%Y %H:%i') AS fecha_recordatorio
      FROM (select @s:=0) as s, mdl_course c
      INNER JOIN mdl_customfield_data cd ON cd.instanceid = c.id
      where c.visible = 1 and cd.fieldid IN (32,33,34,35,36) and c.id = 241"; /*and c.id = 9 or c.id = 3*/

      global $DB;
      $result = $DB->get_records_sql($query);
      //Url
      $url = 'https://calidad.laucmi.telefonicaed.pe/course/view.php?id=';

      foreach ($result as $it) {
        $urltemp = $url.$it->id;
        $id = $it->id;
        // fecha inicios
        $fechaInicio = $it->fechainicio;
        //  Fecha fin
        $fechaFinal = $it->fechafinal;
        //Envio de notificacion
        $fecha  = $it->fecha_recordatorio;

        // Query de estudiante que valida si tiene el curso iniciado, no iniciado o finalizado
        $query3 = "SELECT @s:=@s + 1 id, c.fullname as curso, concat(u.firstname,' ',u.lastname) nombre, u.email, c.shortname, c.fullname,
        			       CASE when compl.num<=0 or compl.num is null THEN 'no iniciado'
        			       when  compl.num=act.num then 'completado' else 'iniciado'
        			       END as estado

        			       from (select @s:=0) as s, mdl_user u
                     INNER JOIN mdl_role_assignments as asg on asg.userid = u.id
                     INNER JOIN mdl_context as con on asg.contextid = con.id
                     INNER JOIN mdl_role r on asg.roleid = r.id
        			       inner join (Select distinct ue.id, ue.userid, e.courseid
        			       from mdl_user_enrolments ue
        			       inner join mdl_enrol e on ue.enrolid=e.id
        			       GROUP BY ue.userid,e.courseid
        			       ) as usrcourse
        			       on u.id=usrcourse.userid
                     left join (Select gi.courseid,gg.userid,gg.finalgrade
        			       	from mdl_grade_items gi
        			       	inner join mdl_grade_grades gg
        			       	on gg.itemid=gi.id
        			       	where gi.itemtype='course')as notas
        			       	on u.id=notas.userid and usrcourse.courseid=notas.courseid
        			       	left join(Select count(id)as num,course from mdl_course_completion_criteria
        			       	group by course) as act
        			       	on act.course=usrcourse.courseid
        			       	left join mdl_course_completions cc
        			       	on u.id=cc.userid and usrcourse.courseid=cc.course
        			       	left join (Select userid,course,count(id)as num from mdl_course_completion_crit_compl
        			       	group by course, userid)as compl
        			       	on compl.userid=u.id and compl.course=usrcourse.courseid
        			       	left join mdl_course c
        			       	on c.id =usrcourse.courseid and con.instanceid = c.id
                      where c.id = '$id' and r.shortname = 'student'
        				      ORDER BY c.id";

        $result3 = $DB->get_records_sql($query3);


          echo '<pre>';
            print_r($result3);
          echo '</pre>';
          echo $fecha; 

          echo $fecha; 
          foreach ($result3 as $it3) {
            $nombre = $it3->nombre;
            $estados = $it3->estado;
            $subject = $it3->fullname; 
            $body = $urltemp;

            // variables para asignar los correos, estas son funciones de moodle
            $emailuser->email = $it3->email;
            $emailuser->id = -99;
            $emailuser->maildisplay = true;
            $emailuser->mailformat = 1;

            date_default_timezone_set("America/Guatemala");
            $fechaAct = date("d/m/Y H:i"); //w para los dias de la semana
            // $fechaViernes = date("w H:i");

            // 5 7:00

            //Imagen 
            $String ="<img src='http://54.161.158.96/local/img/banner.jpg'";  

            //Texto para el recordatorio
            $string1 = ""; 
            $string1 .= $String."\n";
            $string1 .= "<br>"; 
            $string1 .= "<br>"; 
            $string1 .= "<div style='color: orange; font-size: 18px; font-family: Century Gothic;'> $nombre </div>";
            $string1 .= "<br>"; 
            $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Te recordamos que debes completar el curso <span style= 'color: orange; font-size: 16px; font-family: Century Gothic;'> $subject, </span> tienes hasta <span style= 'color: orange; font-size: 16px; font-family: Century Gothic;'> $fechaFinal </span> para realizarlo. </div>";
            $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Haz click en el siguiente enlace para ingresar al curso $body </div>";
            $string1 .= "<br>"; 
            $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Para que tu asistencia al curso sea tomada en cuenta, recuerda que debes responder la encuesta de satisfacción. \n </div>";
            $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Cualquier duda o comentario puedes escribirnos a cmi-laucmi@somoscmi.com \n </div>";
            $string1 .= "<br>"; 
            $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Atentamente, \n </div>";
            $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> laUcmi \n </div>";

            //Comparaciones de fechas para el envio del correo electronico
            if($fechaAct == $fecha){
              if($estados == 'no iniciado' || $estados == 'iniciado'){
                $email = email_to_user($emailuser,'laUcmi','Recuerda completar tu curso '.$subject,$string1);
                echo "Correo enviado";
              }
            }else{
                echo "Correo no enviado";
            }
          }
        }
      }
    }
?>
