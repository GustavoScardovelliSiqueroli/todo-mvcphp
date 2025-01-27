<?php

namespace app\services;

use app\models\TaskModel;
use utils\ViewUtils;
use configs\ConnectionDB;
use PDO;
use utils\UtilsGS;

final class TaskService
{

    private TaskModel|null $taskModel;
    private ConnectionDb $connectionDB;
    private string $dbTasktable = "tasks";

    public function __construct(TaskModel $taskModel = null, string|null  $dbTasktable = null)
    {
        $this->taskModel = $taskModel;
        $this->connectionDB = new ConnectionDB();
        if (!$dbTasktable == null) {
            $this->dbTasktable = $dbTasktable;
        }
    }

    public function save(TaskModel $taskModel)
    {

        $this->taskModel = $taskModel;
        $utils = new UtilsGS();
        $pdoConnected = $this->connectionDB->connect();

        $query = "INSERT INTO {$this->dbTasktable} (id_task, id_user, title) VALUES (:id_task, :id_user, :title)";
        $stmt = $pdoConnected->prepare($query);

        $newUUID = $utils->uuidv4();
        session_start();
        $this->taskModel->idUser = $_SESSION["user"];

        $stmt->bindParam(':id_task', $newUUID);
        $stmt->bindParam(':id_user', $this->taskModel->idUser);
        $stmt->bindParam(':title', $this->taskModel->title);

        $stmt->execute();

        header("Location: ./tasks");
    }

    public function getAll($user)
    {
        $pdoConnected = $this->connectionDB->connect();
        $stmt = $pdoConnected->prepare("SELECT * FROM tasks WHERE id_user = :id_user");
        $stmt->execute(["id_user" => $user]);

        $returnList = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $task = new TaskModel($row["id_task"], $row["id_user"], $row["title"], $row["done"]);
            $returnList[] = $task->getArray();
        }
        return $returnList;
    }


    public static function renderTaksUnit(array $arrayTaks)
    {
        $returnTasksRender = [];
        foreach ($arrayTaks as $taskArray) {
            $taskDoneClass = "";
            if ($taskArray["done"] != false){
                $taskDoneClass = "done";
            }
            $returnTasksRender[] = ViewUtils::render(
                'items/task-item',
                ["taskTitle" => $taskArray["title"], "taskId" => $taskArray["idTask"], "done"=>$taskDoneClass]
            );
        }
        return implode(" ", $returnTasksRender);
    }

    public function deleteByID($id){
        $query = "DELETE FROM {$this->dbTasktable} where id_task=:id_task";
        $pdoConnected = $this->connectionDB->connect();
        $stmt = $pdoConnected->prepare($query);
        $stmt->execute(["id_task" => $id]);
        header("Location: ./tasks");

    }

    public function doTaks($id){
        $query = "UPDATE {$this->dbTasktable} SET done=1 where id_task=:id_task";
        $pdoConnected = $this->connectionDB->connect();
        $stmt = $pdoConnected->prepare($query);
        $stmt->execute(["id_task" => $id]);
        header("Location: ./tasks");

    }
}
