<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

define('GLPI_ROOT', '..');
include (GLPI_ROOT . "/inc/includes.php");

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["softwares_id"])) {
   $_GET["softwares_id"] = "";
}

$version = new SoftwareVersion();

/// TODO clean install process : create file for computer_softwareversion actions
/// Begin of old management of install :
if (isset($_REQUEST["install"])) {
   checkRight("software","w");
   installSoftwareVersion($_REQUEST["computers_id"],$_REQUEST["softwareversions_id"]);
   Event::log($_REQUEST["computers_id"], "computers", 5, "inventory",
              $_SESSION["glpiname"]." installed software.");
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_GET["uninstall"])) {
   checkRight("software","w");
   uninstallSoftwareVersion($_GET["id"]);
   Event::log($_GET["computers_id"], "computers", 5, "inventory",
              $_SESSION["glpiname"]." uninstalled software.");
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_POST["deleteinstalls"])) {
   checkRight("software","w");

   foreach ($_POST["item"] as $key => $val) {
      if ($val == 1) {
         uninstallSoftwareVersion($key);
         Event::log($_POST["softwares_id"], "software", 5, "inventory",
                    $_SESSION["glpiname"]." uninstalled software for several computers.");
      }
   }
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_POST["moveinstalls"])) {
   checkRight("software","w");
   foreach ($_POST["item"] as $key => $val) {
      if ($val == 1 && $_POST['versionID'] > 0) {
         updateInstalledVersion($key, $_POST['versionID']);
         Event::log($_POST["softwares_id"], "software", 5, "inventory",
                    $_SESSION["glpiname"]." change version of versions installed on computers.");
      }
   }
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_POST["uninstall_license"])) {
   checkRight("software","w");
   foreach ($_POST as $key => $val) {
      if (preg_match("/license_([0-9]+)/",$key,$ereg)) {
         $input["id"] = $ereg[1];
         uninstallSoftwareVersion($input["id"]);
      }
   }
   Event::log($_POST["computers_id"], "computers", 5, "inventory",
              $_SESSION["glpiname"]." uninstalled software.");
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_POST["install_license"]) && isset($_POST["computers_id"])) {
   checkRight("software","w");
   foreach ($_POST as $key => $val) {
      if (preg_match("/version_([0-9]+)/",$key,$ereg)) {
         if ($ereg[1] > 0) {
            installSoftwareVersion($_POST["computers_id"],$ereg[1]);
         }
      }
   }
   Event::log($_POST["computers_id"], "computers", 5, "inventory",
              $_SESSION["glpiname"]." installed software.");
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_GET["back"])) {
   glpi_header($_GET["back"]);

/// End of old management of install
} else if (isset($_POST["add"])) {
    $version->check(-1,'w',$_POST);

   if ($newID = $version->add($_POST)) {
      Event::log($_POST['softwares_id'], "software", 4, "inventory",
                 $_SESSION["glpiname"]." ".$LANG['log'][82]." $newID.");
      glpi_header($CFG_GLPI["root_doc"]."/front/software.form.php?id=".
                  $version->fields['softwares_id']);
   } else {
      glpi_header($_SERVER['HTTP_REFERER']);
   }

} else if (isset($_POST["delete"])) {
   $version->check($_POST['id'],'w');

   $version->delete($_POST);
   Event::log($version->fields['softwares_id'], "software", 4, "inventory",
              $_SESSION["glpiname"]." ".$LANG['log'][84]." ".$_POST["id"]);
   glpi_header($CFG_GLPI["root_doc"]."/front/software.form.php?id=".$version->fields['softwares_id']);

} else if (isset($_POST["update"])) {
   $version->check($_POST['id'],'w');

   $version->update($_POST);
   Event::log($version->fields['softwares_id'], "software", 4, "inventory",
              $_SESSION["glpiname"]." ".$LANG['log'][83]." ".$_POST["id"]);
   glpi_header($_SERVER['HTTP_REFERER']);

} else {
   commonHeader($LANG['Menu'][4],$_SERVER['PHP_SELF'],"inventory","software");
   $version->showForm($_SERVER['PHP_SELF'],$_GET["id"],$_GET["softwares_id"]);
   commonFooter();
}

?>
