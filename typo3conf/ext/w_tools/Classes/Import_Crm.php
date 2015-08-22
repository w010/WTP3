<?php


//use \TYPO3\CMS\Core\Utility\GeneralUtility;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('w_tools').'Classes/Import.php');



class tx_wtools_import_crm extends tx_wtools_import {


	/**
	 * prepare and parse data from files
	 * get persons, groups, relations and companies
	 */
	protected function _prepareData()    {
		$success = false;

		// clear existing data - 1. remove all Personen (tt_address) and 2. it's relations (with tablenames = 'ExportDynamicsCRM')
		$this->db()->exec_DELETEquery('tt_address', 'pid = '.intval($this->extConf['pid']));
		//$this->db()->exec_DELETEquery('tt_address_group_mm', 'tablenames = "ExportDynamicsCRM"');
		$this->db()->exec_DELETEquery('tt_address_group_mm', 'sorting > 1');
		//$this->log('FLUSH: Person (4Personen) clean import (tt_address table - where pid, tt_address_group_mm - where tablenames="ExportDynamicsCRM")');
		$this->log('FLUSH: Person (4Personen) clean import (tt_address table - where pid, tt_address_group_mm - where sorting = 2');


		foreach ($this->_pathFiles as $file)   {
			if (preg_match('/exportGroepen/', $file, $m2))  {
				//debugster($m2);
				// table name or just label. number to proper order in iteration - groups must be inserted before persons
				$this->data['1Groups'] = $this->XmlObj->getXmlFromFile( $this->_workingDir . $file );
				if ($this->data['1Groups'])   $success = true;
				$this->counter['1Groups'] = 0;
			}

			if (preg_match('/exportGroepsdeelnemingen/', $file, $m3))   {
				$this->data['2GroupRelations'] = $this->XmlObj->getXmlFromFile( $this->_workingDir . $file );
				if ($this->data['2GroupRelations'])   $success = true;
				$this->counter['2GroupRelations'] = 0;
			}

			if (preg_match('/exportBedrijven/', $file, $m1))    {
				$this->data['3Bedrijven'] = $this->XmlObj->getXmlFromFile( $this->_workingDir . $file );
				if ($this->data['3Bedrijven'])   $success = true;
				$this->counter['3Bedrijven'] = 0;
			}

			if (preg_match('/exportPersonen/', $file, $m4)) {
				$this->data['4Personen'] = $this->XmlObj->getXmlFromFile( $this->_workingDir . $file );
				if ($this->data['4Personen'])   $success = true;
				$this->counter['4Personen'] = 0;
			}
		}
		// important - order by index, to force valid order of importing - first groups, persons last
		ksort($this->data);
		ksort($this->counter);
		return ['success' => $success];
	}



	/**
	 * save data from input files
	 * all files has same structure, so there's no need to split this method
	 * @return array result
	 */
	protected function _saveData()  {
		$success = true;
		// iterate record types, if more than one, ie. categories
		foreach($this->data as $recordType => $xmlItem)	{
			//debugster($recordType);
				$this->TTr->start('save set', 'save_'.$recordType, 'SAVE_GROUP');
			foreach($xmlItem->BusinessEntities[0]->BusinessEntity as $entity) {
				if ($entity->statecode > 0)
					$this->_removeRecord($entity, $recordType);
				else
					$this->_saveRecord($entity, $recordType);
				//				break;  // stop after first
			}
				$this->TTr->stop('save_'.$recordType, 'SAVE_GROUP');

			$this->log('already existed: ' . $recordType . ' - ' . count($this->alreadyExistsId[$recordType]));

			if ($this->counter[$recordType])
				$this->log('INSERTED items: ' . $recordType . ' - ' . $this->counter[$recordType]);

			if ($this->counter[$recordType.'_updated'])
				$this->log('UPDATED items: ' . $recordType . ' - ' . $this->counter[$recordType.'_updated']);

			if ($this->counter[$recordType.'_mm'])
				$this->log('- MM category relations: ' . $recordType . ' - ' . $this->counter[$recordType.'_mm']);


			// REMOVED LOG. disabled, was used only in person import but now the table is cleared before import
			/*	if ($this->counter[$recordType.'_remove_should'])
					// actually_removed can be greater than should_remove, because there can be for some reason more of records with this xml id imported...
					$this->log('DELETED items: ' . $recordType . ' - should delete: ' . $this->counter[$recordType.'_remove_should'] . ', - actually deleted: ' . $this->counter[$recordType.'_removed_actually'] );

				if ($this->counter[$recordType.'_removed_mm'])
					$this->log('- it\'s MM relations deleted: ' . $recordType . ' - ' . $this->counter[$recordType.'_removed_mm']);*/


			// read groups again
			if ($recordType == '1Groups')   {
				$this->existingInDb['1Groups'] = $this->_getRecords('*', 'tt_address_group', 'AND pid = '.$this->extConf['pid']);
				$this->log('read Groups again from db after import: '.count($this->existingInDb['1Groups']));
			}
		}
		// test page - don't debug in scheduler
		if ($GLOBALS['TSFE']->id == 82) {
			//debugster( $this->data['test'] );
			debugster($this->counter);
			//debugster($this->existingInDb);
			//debugster($this->existingInDb['3Bedrijven']);
			//debugster($this->alreadyExistsId);
			//debugster($this->alreadyExistsId['3Bedrijven']);
		}
		return ['success' => $success, 'notice' => 'data saved'];
	}



	/**
	 * save crm record
	 *
	 * @param        $entity - array or simplexmlobject
	 * @param string $recordType
	 * @return array result
	 */
	protected function _saveRecord($entity, $recordType)	{
		$result = ['notice' => 'check recordType param, case not triggered'];
		switch ($recordType) {
			case '1Groups':
				// insert control
				if (false !== array_search((string) $entity->new_groupid, array_column($this->existingInDb[$recordType], 'description'))) {
					$this->alreadyExistsId[$recordType][] = (string) $entity->new_groupid;
					break;
				}
				$row = $this->_mapRow__tt_address_group($entity);
				// INSERT, if not found
				$result = $this->_insertItem($row, 'tt_address_group', $recordType);
				//debugster($row);
				break;

			case '2GroupRelations':
				// add to data array to later use for group relations
				$row = [
					'new_new_group_contactid' => (string) $entity->new_new_group_contactid,
					'new_groupid' => (string) $entity->new_groupid,
					'contactid' => (string) $entity->contactid
				];
				//debugster($row);
				$this->data['2GroupRelations_parsed'][] = $row;
				break;

			case '3Bedrijven':

				//debugster($entity);
				//debugster((string) $entity->accountid);
				// insert control
				$exists = false;
				if (false !== array_search((string) $entity->accountid, array_column($this->existingInDb[$recordType], 'tx_sitecedris_sw_accountid'))) {
					$this->alreadyExistsId[$recordType][] = (string) $entity->accountid;
					$exists = true;
					//debugster($entity);
					//die('exists');
					//break;
				}
				$row = $this->_mapRow__fe_users($entity);

					// UPDATE. note: inserting disabled
					// http://mantis.kbsystems.pl/view.php?id=4471
					// only updating for now, because not all records (previously imported from excel) has set id and will duplicate when inserted from xml.
				// NOW IS ALSO INSERTING
				if ($exists) {
					unset ($row['username']);
					unset ($row['password']);
					unset ($row['usergroup']);
					unset ($row['pid']);
					unset ($row['crdate']);
					$result = $this->_insertItem($row, 'fe_users', $recordType, '', [], (string) $entity->accountid, 'tx_sitecedris_sw_accountid');
				}
				else	{
					$result = $this->_insertItem($row, 'fe_users', $recordType, '', [], 0, 'tx_sitecedris_sw_accountid');
				}
				break;

			case '4Personen':
				//debugster($entity);

				// insert control - disabled
				// table is cleared before import
				$exists = false;
				/*
				if (false !== array_search((string) $entity->contactid, $this->existingInDb[$recordType])) {
					$this->alreadyExistsId[$recordType][] = (string) $entity->contactid;
					//break;
					$exists = true;
				}
				*/

				$row = $this->_mapRow__tt_address($entity);

				// test - this record has 2 relations: //if ($row['description'] == '82952189-bbe1-db11-810a-0014384ef846') {

				// prepare relations array
				$groupUids = is_array($row['__UNSET_groupUids']) ? $row['__UNSET_groupUids'] : [];
				array_unshift($groupUids, $this->extConf['categoryUid']);   // default as first
				//debugster($groupUids);


				// INSERT / UPDATE
				unset ($row['__UNSET_groupUids']);
				$result = $this->_insertItem($row, 'tt_address', $recordType, 'tt_address_group_mm', $groupUids, $exists ? (string) $entity->contactid : 0, 'description', "", "2");    // 'description' is passed as default, but is not used in this context (person) - is used in update bedrijven as idfield
				break;
		}

		//$this->log('insert: '.implode(',', $this->alreadyExists));
		return $result; //['notice' => 'insert: '.implode(',', $this->alreadyExists)];
	}




	/**
	 * remove control - delete flagged records from database
	 * PROBABLY NOT USED FOR NOW SINCE WE CLEAR WHOLE TABLE
	 *
	 * @param        $entity - array or simplexmlobject
	 * @param string $recordType
	 * @return array result
	 */
	protected function _removeRecord($entity, $recordType)	{
		switch ($recordType) {

			case '4Personen':
				//debugster($entity);
				// if statecode > 0 - delete this record from database
				if ($entity->statecode > 0) {

					$this->counter[$recordType . '_remove_should']++;

					// select given record, get its uid (or uids, may be more with this xml id...) to remove its relations
					$rows = self::db()->exec_SELECTgetRows(
						'uid',
						'tt_address',
						'description = '.self::db()->fullQuoteStr($entity->contactid, 'tt_address'),
						'', '', '', 'uid'    // group, order, limit, index
					);
					//debugster($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);
					//debugster($rows);
					//debugster((string) $entity->contactid);

					// remove record(s) with such xml id (could be more than one)
					self::db()->exec_DELETEquery('tt_address', 'description = '.self::db()->fullQuoteStr($entity->contactid, 'tt_address'));
					//debugster($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);
					$res_removed = self::db()->sql_query('SELECT ROW_COUNT()');
					$removed = array_pop($res_removed->fetch_assoc());
					$this->counter[$recordType . '_removed_actually'] += intval($removed);
					//debugster($removed);
					// remove also their mms
					if ($removed) {
						foreach ($rows as $row) {
							$removedMM = self::db()->exec_DELETEquery('tt_address_group_mm', 'uid_local = ' . intval($row['uid']));
							$this->counter[$recordType . '_removed_mm'] += intval($removedMM);
						}
					}
				}
				break;
		}

		//$this->log('insert: '.implode(',', $this->alreadyExists));
		return true;
	}



	/**
	 * maps xml structure to row array to insert into database
	 *
	 * @param SimpleXMLElement $entity
	 * @return array
	 */
	protected function _mapRow__tt_address(SimpleXMLElement &$entity)	{

		//debugster($entity);

		/*
		 * Find group relation for person
		 *
		 * There are such id fields in person: address1_addressid, address2_addressid, contactid
		 * and only contactid seems to relate to anything. rest are not found anywhere else, so we're not using them.
		 * There are such id fields in relation: new_new_group_contactid, new_groupid, contactid
		 * and new_new_group_contactid seems not be used for anything.
		 *
		 * Relation looks like this:
		 * Person.contactid <- Relation.contactid  =  Relation.new_groupid -> Group.new_groupid
		 */

		// use mm data to find and set group relation
		// find array index of relation, get the group id from it, finally get uid (collect all group uids)
		// $relationIndex = array_search((string) $entity->contactid, array_column($this->data['2GroupRelations_parsed'], 'contactid'));
		// find all indexes - there could be many group relations
		$relationIndexes = array_keys( array_column($this->data['2GroupRelations_parsed'], 'contactid'), (string) $entity->contactid );
		//debugster($relationIndexes);

		foreach ($relationIndexes as $relationIndex)  {
//		if (is_integer($relationIndex)) {
			// xml id!
			$groupid = $this->data['2GroupRelations_parsed'][$relationIndex]['new_groupid'];
			$groupIndex = array_search($groupid, array_column($this->existingInDb['1Groups'], 'description'));
			// group record
			// debugster($this->existingInDb['1Groups'][$groupIndex]);

			// typo sys uids!
			$groupUids[] = $this->existingInDb['1Groups'][$groupIndex]['uid'];
		}


		// relation test & find - take a look on last one
			/*
			$this->data['test']['_hits__address1_addressid__in__new_new_group_contactid'] += array_search((string) $entity->address1_addressid, array_column($this->data['2GroupRelations_parsed'], 'new_new_group_contactid')) ? 1 : 0;
			$this->data['test']['_hits__address1_addressid__in__new_groupid'] += array_search((string) $entity->address1_addressid, array_column($this->data['2GroupRelations_parsed'], 'new_groupid')) ? 1 : 0;
			$this->data['test']['_hits__address1_addressid__in__contactid'] += array_search((string) $entity->address1_addressid, array_column($this->data['2GroupRelations_parsed'], 'contactid')) ? 1 : 0;
			$this->data['test']['_hits__address2_addressid__in__new_new_group_contactid'] += array_search((string) $entity->address2_addressid, array_column($this->data['2GroupRelations_parsed'], 'new_new_group_contactid')) ? 1 : 0;
			$this->data['test']['_hits__address2_addressid__in__new_groupid'] += array_search((string) $entity->address2_addressid, array_column($this->data['2GroupRelations_parsed'], 'new_groupid')) ? 1 : 0;
			$this->data['test']['_hits__address2_addressid__in__contactid'] += array_search((string) $entity->address2_addressid, array_column($this->data['2GroupRelations_parsed'], 'contactid')) ? 1 : 0;
			$this->data['test']['_hits__contactid__in__new_new_group_contactid'] += array_search((string) $entity->contactid, array_column($this->data['2GroupRelations_parsed'], 'new_new_group_contactid')) ? 1 : 0;
			$this->data['test']['_hits__contactid__in__new_groupid'] += array_search((string) $entity->contactid, array_column($this->data['2GroupRelations_parsed'], 'new_groupid')) ? 1 : 0;
			$this->data['test']['_hits__contactid__in__contactid'] += array_search((string) $entity->contactid, array_column($this->data['2GroupRelations_parsed'], 'contactid')) ? 1 : 0;*/
			//	debugster( $this->data['test'] );

		//		debugster($this->data['2GroupRelations_parsed']);

		$row = [
			// system
			'tstamp' => $this->scriptStartStamp,
			//'crdate' => strtotime($entity->createdon),    // doesn't have such
			'pid' => $this->extConf['pid'],
			'module_sys_dmail_html' => 1,
			'email' => (string) $entity->emailaddress1,
			'name' => (string) $entity->yomifullname,
			'first_name' => (string) $entity->new_voornaam,
			'last_name' => (string) $entity->lastname,
			'country' => (string) $entity->address1_country,
			'description' => (string) $entity->contactid,    // main id used from this field!
			'__UNSET_groupUids' => $groupUids   // only for setting mm in caller, unset before insert, there's no such field
			//'overall_official' => json_encode($entity->xpath('overall_official')),    // example
		];

		//if (DEVS)
		//	$row['email'] = 'wolo.wolski+'.mt_rand(0,12345346).'@gmail.com';

		return $row;
	}

	/**
	 * maps xml structure to row array to insert into database
	 *
	 * @param SimpleXMLElement $entity
	 * @return array
	 */
	protected function _mapRow__tt_address_group(SimpleXMLElement &$entity)	{
		return [
			// system
			'tstamp' => $this->scriptStartStamp,
			'crdate' => (int) strtotime($entity->createdon),
			'pid' => $this->extConf['pid'],

			'title' => (string) $entity->new_name,
			'description' => (string) $entity->new_groupid     // MAIN ID used from this field!
		];
	}


	/**
	 * maps xml structure to row array to insert into database
	 *
	 * @param SimpleXMLElement $entity
	 * @return array
	 */
	protected function _mapRow__fe_users(SimpleXMLElement &$entity)	{
		return [
			// system
			'tstamp' => $this->scriptStartStamp,
			'crdate' => (int) strtotime($entity->createdon),
			//'pid' => $this->extConf['pid'],

			// not shown on page by default. need to set category, which auto moves to proper storage.
			'pid' => 107,
			'usergroup' => '3',
			'password' => md5(chr(mt_rand(1,256)) . mt_rand(1,10000000) . chr(mt_rand(1,256))),
			'module_sys_dmail_newsletter' => 1,
			'module_sys_dmail_html' => 1,

			'name' => (string) $entity->name,
			'username' => (string) $entity->name,   // unset on update
			'tx_sitecedris_sw_accountid' => (string) $entity->accountid,     // MAIN ID used from this field!
			'tx_sitecedris_sw_provincie' => (string) $entity->address1_stateorprovince,
			'tx_sitecedris_sw_postadres' => (string) $entity->address1_postalcode,
			'telephone' => (string) $entity->telephone1,
			'country' => (string) $entity->address1_country,
			'email' => (string) $entity->emailaddress1,
			'city' => (string) $entity->address1_city,
			'zip' => (string) $entity->address1_postalcode,
			'fax' => (string) $entity->fax,
			'www' => (string) $entity->websiteurl,
		];
	}


	/**
	 * @param array  $row              - item to insert into db
	 * @param string $table
	 * @param string $recordType
	 * @param string $mmTable
	 * @param array  $mmUidsForeign    - uids of foreign table, ie. categories (typo uids!)
	 * @param int    $updateExistingId - id (possible not uid) of current record. 0 means insert
	 * @param string $idField          - name of db field with id
	 * @param string $tablenames       - tablenames column in mm table. used in where clause to group relations to clear table before import
	 * @param int $mmSorting           - mm relation sorting. see above - where clause
	 * @return mixed
	 */
	protected function _insertItem($row, $table = 'tt_news', $recordType = 'default', $mmTable = 'tt_news_cat_mm', $mmUidsForeign = [], $updateExistingId = 0, $idField = 'description', $tablenames = '', $mmSorting = 2)	{

		// if using method with duplicate key update, omit this
		// insert if not updating
		if ($updateExistingId === 0) {

			// neccessary to make valid query - only if manual build
			$row = self::db()->fullQuoteArray($row, $table, false);
			// query build
			$insert_fields = implode(',', array_keys($row));
			$insert_values = implode(',', $row);

			// $update_pairs = implode(',', $update_pairs_array);
			// If you specify ON DUPLICATE KEY UPDATE, and a row is inserted that would cause a duplicate value in a UNIQUE index or PRIMARY KEY, an UPDATE of the old row is performed.
			// INSERT INTO table (`uid`, `pid`) VALUES (1, 1) ON DUPLICATE KEY UPDATE uid = 1, pid = 1
			// $query = "INSERT INTO {$this->table} ({$insert_fields}) VALUES ({$insert_values}) ON DUPLICATE KEY UPDATE {$update_pairs}";
			$query = "INSERT INTO {$table} ({$insert_fields}) VALUES ({$insert_values})";

			//debugster($query);
			$result = self::db()->sql_query($query);
			if ($uidLocal = self::db()->sql_insert_id())
				$this->counter[$recordType]++;


			// insert mm relation records
			//debugster($GLOBALS['TYPO3_DB']->sql_insert_id());
			if (is_array($mmUidsForeign)  &&  $uidLocal)
				//if ($tablenames)    die($tablenames);
				//foreach (explode(',', $mmUidsForeign) as $mmUidForeign)
				foreach ($mmUidsForeign as $mmUidForeign)
					if (intval($mmUidForeign)) {
						$this->_insertMmRelation($mmTable, $uidLocal, $mmUidForeign, $tablenames, $mmSorting);
						$this->counter[$recordType.'_mm'] ++;
					}
		}

		// update record (can be string)
		else if ($updateExistingId)   {
			//debugster($row);
			//debugster((string) $updateExistingId);
				// one update could take about 5-10 ms, so calculate this if import takes too long
				//$this->TTr->start('update_'.$updateExistingId, 'update_'.$updateExistingId);

			unset ($row['crdate']);

			// (cedris crm - id for news is currently kept in description field! as default for this method)
			$result = self::db()->exec_UPDATEquery(
				$table,
				$idField.' = "'.$updateExistingId.'"',
				$row
			);
			$this->counter[$recordType.'_updated'] += intval($result);

				//$this->TTr->stop('update_'.$updateExistingId);

			//debugster($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);
			//debugster($this->counter);
		}



		// clean up
		unset($row);
		unset($insert_fields);
		unset($insert_values);
		//unset($update_pairs);
		unset($query);



		// $this->TTr->stop();

		return $result;
	}






	/**
	 * read existing database data to prevent duplicates and set relations
	 */
	protected function _readExistingData()   {
		// get 1Groups - whole records would be helpful
		$this->existingInDb['1Groups'] = $this->_getRecords('*', 'tt_address_group', 'AND pid = '.intval($this->extConf['pid']));
		$this->log('LOAD current: total Group records in db: '.count($this->existingInDb['1Groups']));
		//debugster($this->existingInDb['1Groups']);

		// get 3Bedrijven
		$this->existingInDb['3Bedrijven'] = $this->_getRecords('*', 'fe_users', 'AND pid IN (45,107)', 'AND NOT deleted AND NOT disable');   // pid should be in conf, but probably will never change
		$this->log('LOAD current: total Bedrijven records in db: '.count($this->existingInDb['3Bedrijven']));

		// get 4Personen - only identifiers, which is kept in description field
		$this->existingInDb['4Personen'] = $this->_getExistingRecordsIds('description', 'tt_address', 'AND pid = '.$this->extConf['pid']);
		$this->log('LOAD current: total Person records in db: '.count($this->existingInDb['4Personen']));
	}


	/**
	 * note that this could possible not work with fe_users table (which doesn't have deleted, but disabled)
	 *
	 * @param        $fields
	 * @param        $table
	 * @param        $where
	 * @param string $enableFields
	 * @return array
	 */
	private function _getRecords($fields, $table, $where, $enableFields = 'AND NOT deleted AND NOT hidden') {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$fields,
			$table,
			'1=1 '.$enableFields.' '.$where
		);
		$rows = [];
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$rows[] = $row;
		}
		return $rows;
	}


	/**
	 * called by scheduler to display in backend task list
	 * @return string
	 */
	public function getAdditionalInformation()  {
		$this->init();
		$this->_prepareWorkingDirAndGetFiles();
		return 'CRM: files in directory: '.implode(', ', $this->_pathFiles);
	}

}


