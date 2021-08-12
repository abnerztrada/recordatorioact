<?php
namespace tool_recordatorio\task;

// use tool_recordatorio\models;
use tool_recordatorio\models\correo;
// use tool_recordatorio\models\report;
class recordatorio extends \core\task\scheduled_task
{
    /**
     * return name of task for admin panel.
     *
     * @return string name
     */
    public function get_name()
    {
        return get_string('cronenroll', 'tool_recordatorio');
    }

    /**
     * method to execute by cron task.
     */
    public function execute()
    {
      // mtrace("Hola mundo");
      global $CFG;
      // Funcion para el recordatorio
      $correo_envio = new correo();
      $correo_envio->correo_envio();


    }
}
