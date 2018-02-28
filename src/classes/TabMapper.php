<?php

class TabMapper extends Mapper
{
    public function getTabs() {
        $sql = "SELECT * FROM tabs ORDER BY date ASC";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new TabEntity($row, false);
        }
        return $results;
    }

    public function save(TabEntity $tab) {

        // No idea how to get PDO to insert the variables properly,
        // so we're doing it this way.

        $date = $tab->getDate();
        $timestamp = $tab->getTimestampClean();
        $host = $tab->getHost();

        $sql = "INSERT INTO tabs (url, date, host, timestamp, icon, title) 
                VALUES (:url, :date, :host, :timestamp, :icon, :title)
                    ON DUPLICATE KEY UPDATE 
                    date='$date', timestamp='$timestamp', host='$host';";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "url" => $tab->getURLClean(),
            "date" => $date,
            "host" => $host,
            "timestamp" => $timestamp,
            "icon" => $tab->getIcon(),
            "title" => $tab->getTitle()
        ]);

        if(!$result) {
            throw new Exception("could not save record");
        }
    }

    public function deleteURL($url) {
        $sql = "delete from tabs where url='$url'";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute();

        if(!$result) {
            throw new Exception("could not delete record");
        }
    }
}

?>