<?php

/**
 * This is a sample class showing how to define a user function
 * returning a list of recipients. To use it, put 'user_testList_tca->myRecipientList' as
 * itemsProcFunc value when creating a Recipient List of type "User function".
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Tx_DirectMailUserfunc_Samples_TestListTca {

	/**
	 * Returns a list of recipients.
	 *
	 * @param array $params User parameters
	 * @param tx_directmail_recipient_list|tx_directmail_dmail $pObj Parent object
	 * @return array
	 */
	public function myRecipientList(array &$params, $pObj) {
		// Add tt_address #4 to the recipient list
		$params['lists']['tt_address'][] = 4;

		// Add frontend user #1 to the recipient list
		$params['lists']['fe_users'][] = 1;

		// Retrieve user parameters
		$userParameters = $params['userParams'];

		$sizeOfRecipientList = isset($userParameters['size']) ? intval($userParameters['size']) : 2;
		for ($i = 0; $i < $sizeOfRecipientList; $i++) {
			$name = !empty($userParameters['name']) ? $userParameters['name'] : 'John Doo';
			$name .= ' #' . $i;
			$email = !empty($userParameters['email']) ? $userParameters['email'] : 'john.doo@hotmail.com';
			list($username, $domain) = explode('@', $email, 2);
			$email = $username . '-' . $i . '@' . $domain;

			$params['lists']['PLAINLIST'][] = array('name' => $name, 'email' => $email);
		}
	}

	/**
	 * Returns an array of field definitions for additional parameters.
	 *
	 * @param string $methodName
	 * @return array|NULL TCA or NULL if no additional parameters are needed
	 */
	public function getWizardFields($methodName) {
		$additionalParameters = array(
			'columns' => array(
				'size' => array(
					'label' => 'LLL:EXT:direct_mail_userfunc/Resources/Private/Language/locallang_db.xml:myRecipientListTca.size',
					'config' => array(
						'type' => 'input',
						'size' => '5',
						'max' => '5',
						'eval' => 'int',
						'default' => 1,
						'range' => array(
							'lower' => 1,
						)
					)
				),
				'name' => array(
					'label' => 'LLL:EXT:direct_mail_userfunc/Resources/Private/Language/locallang_db.xml:myRecipientListTca.name',
					'config' => array(
						'type' => 'input',
						'size' => '20',
						'eval' => 'trim',
					)
				),
				'email' => array(
					'label' => 'LLL:EXT:direct_mail_userfunc/Resources/Private/Language/locallang_db.xml:myRecipientListTca.email',
					'config' => array(
						'type' => 'input',
						'size' => '20',
						'eval' => 'trim',
					)
				),
			),
			'types' => array(
				'5' => array(
					'showitem' => 'size,
						--palette--;LLL:EXT:direct_mail_userfunc/Resources/Private/Language/locallang_db.xml:myRecipientListTca.palette.person;person'
				)
			),
			'palettes' => array(
				'person' => array(
					'showitem' => 'name, email',
					'canNotCollapse' => 1,
				),
			)
		);

		return $additionalParameters;
	}

}
