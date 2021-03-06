<?php

/**
 * LettersOut CRMEntity class
 * @package YetiForce.CRMEntity
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class LettersOut extends CRMEntity
{

	public $table_name = 'vtiger_lettersout';
	public $table_index = 'lettersoutid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_lettersoutcf', 'lettersoutid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_lettersout', 'vtiger_lettersoutcf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_lettersout' => 'lettersoutid',
		'vtiger_lettersoutcf' => 'lettersoutid'];

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Number' => ['lettersout', 'number'],
		'Title' => ['lettersout', 'title'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'Created Time' => ['crmentity', 'createdtime'],
	];
	public $list_fields_name = [
		/* Format: Field Label => fieldname */
		'Number' => 'number',
		'Title' => 'title',
		'Assigned To' => 'assigned_user_id',
		'Created Time' => 'createdtime',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['number', 'title', 'assigned_user_id', 'createdtime'];
	// Make the field link to detail view from list view (Fieldname)
	public $list_link_field = 'title';
	// For Popup listview and UI type support
	public $search_fields = [
		'Number' => ['lettersout', 'number'],
		'Title' => ['lettersout', 'title'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'Created Time' => ['crmentity', 'createdtime'],
	];
	public $search_fields_name = [
		'Number' => 'number',
		'Title' => 'title',
		'Assigned To' => 'assigned_user_id',
		'Created Time' => 'createdtime',
	];
	// For Popup window record selection
	public $popup_fields = ['title'];
	// For Alphabetical search
	public $def_basicsearch_col = 'title';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'title';
	// Required Information for enabling Import feature
	public $required_fields = ['title' => 1];
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'title', 'assigned_user_id'];

	/**
	 * Apply security restriction (sharing privilege) query part for List view.
	 */
	public function getListViewSecurityParameter($module)
	{
		$current_user = vglobal('current_user');
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

		$sec_query = '';
		$tabid = \App\Module::getModuleId($module);

		if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabid] == 3) {

			$sec_query .= " && (vtiger_crmentity.smownerid in($current_user->id) || vtiger_crmentity.smownerid IN
                    (
                        SELECT vtiger_user2role.userid FROM vtiger_user2role
                        INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid
                        INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid
                        WHERE vtiger_role.parentrole LIKE '" . $current_user_parent_role_seq . "::%'
                    )
                    || vtiger_crmentity.smownerid IN
                    (
                        SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per
                        WHERE userid=" . $current_user->id . " && tabid=" . $tabid . "
                    )
                    OR
                        (";

			// Build the query based on the group association of current user.
			if (sizeof($current_user_groups) > 0) {
				$sec_query .= " vtiger_groups.groupid IN (" . implode(",", $current_user_groups) . ") || ";
			}
			$sec_query .= " vtiger_groups.groupid IN
                        (
                            SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid
                            FROM vtiger_tmp_read_group_sharing_per
                            WHERE userid=" . $current_user->id . " and tabid=" . $tabid . "
                        )";
			$sec_query .= ")
                )";
		}
		return $sec_query;
	}

	/**
	 * Transform the value while exporting
	 */
	public function transformExportValue($key, $value)
	{
		return parent::transformExportValue($key, $value);
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param string $moduleName Module name
	 * @param string $eventType Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($eventType === 'module.postinstall') {
			$ModuleInstance = CRMEntity::getInstance($moduleName);
			\App\Fields\RecordNumber::setNumber($moduleName, 'LI', '1');
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(['LettersOut']);
			}
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(\App\Module::getModuleId($moduleName));
			$dbCommand = \App\Db::getInstance()->createCommand();
			$dbCommand->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
			$dbCommand->update('vtiger_field', ['summaryfield' => 1], ['tablename' => 'vtiger_lettersout', 'columnname' => 'title'])->execute();
			$dbCommand->update('vtiger_field', ['summaryfield' => 1], ['tablename' => 'vtiger_lettersout', 'columnname' => 'smownerid'])->execute();
			$dbCommand->update('vtiger_field', ['summaryfield' => 1], ['tablename' => 'vtiger_lettersout', 'columnname' => 'lout_type_ship'])->execute();
			$dbCommand->update('vtiger_field', ['summaryfield' => 1], ['tablename' => 'vtiger_lettersout', 'columnname' => 'lout_type_doc'])->execute();
			$dbCommand->update('vtiger_field', ['summaryfield' => 1], ['tablename' => 'vtiger_lettersout', 'columnname' => 'date_adoption'])->execute();
			$dbCommand->update('vtiger_field', ['summaryfield' => 1], ['tablename' => 'vtiger_lettersout', 'columnname' => 'relatedid'])->execute();
		}
	}
}
