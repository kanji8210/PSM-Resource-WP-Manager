<?php
class PSM_Resource_Manager_Deactivator {
    public static function deactivate() {
        flush_rewrite_rules();
    }
}
