<?php

class TabMapper extends Mapper
{
    public function checkAuth($key) {
        if(!$key) {
            return false;
        }
        $hash = hash('sha256', $key);
        $sql = "SELECT * FROM users WHERE hash='$hash'";
        
        $stmt = $this->db->query($sql);

        while($row = $stmt->fetch()) {
            return true;
        }
        return false;
    }

    public function getUser($key) {
        if(!$key) {
            return false;
        }
        $hash = hash('sha256', $key);
        $sql = "SELECT username FROM users WHERE hash='$hash'";

        $stmt = $this->db->query($sql);

        while($row = $stmt->fetch()) {
            return $row['username'];
        }
        return false;
    }

    public function getTabs($user) {
        $sql = "SELECT * FROM tabs WHERE user='$user' ORDER BY date ASC";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new TabEntity($row, false);
        }
        return $results;
    }

    public function save(TabEntity $tab, $user) {

        // No idea how to get PDO to insert the variables properly,
        // so we're doing it this way.
        $date = $tab->getDate();
        $timestamp = $tab->getTimestampClean();
        $host = $tab->getHost();

        $sql = "INSERT INTO tabs (url, date, host, timestamp, icon, title, user) 
                VALUES (:url, :date, :host, :timestamp, :icon, :title, :user)
                    ON DUPLICATE KEY UPDATE 
                    date='$date', timestamp='$timestamp', host='$host';";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "url" => $tab->getURLClean(),
            "date" => $date,
            "host" => $host,
            "timestamp" => $timestamp,
            "icon" => $tab->getIcon(),
            "title" => $tab->getTitle(),
            "user" => $user
        ]);

        if(!$result) {
            throw new Exception("could not save record");
        }
    }

    public function deleteURL($url, $user) {
        $sql = "delete from tabs where url='$url' and user='$user'";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute();

        if(!$result) {
            throw new Exception("could not delete record");
        }
    }
}

?>