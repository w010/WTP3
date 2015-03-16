
plugin.tx_srfeuserregister_pi1 {
	pid = 117
	loginPID = 88
	editPID = 158
	confirmPID = 131
	registerPID = 89

	tx_sitexxx.organizatorsPID = 143

	userGroupUponRegistration = 1
	userGroupAfterConfirmation = 2

	# ext:site_xxx hook
	userGroupAfterConfirmation_withOrganization = 3

	useMd5Password = 0
	#usernameAtLeast = 4

	useLocalization = 1

	formFields = username,password,tx_sitexxx_addevents,tx_sitexxx_organizer
	requiredFields = email,password

	useEmailAsUsername = 1

	enablePreviewRegister = 0
	enableAutoLoginOnConfirmation = 1
	autoLoginRedirect_url = /

	enableAdminNotifyConfirmation = 0
	enableAdminNotifyOnApprove = 0
	enableAdminNotifyOnRefuse = 0
	enableAdminNotifyOnRegister = 0
	enableAdminNotifyOnUpdate = 0
	enableAdminNotifyOnDelete = 0
	enableAdminNotifyOnAdminAccept = 0
	enableAdminNotifyOnAdminRefuse = 0

	pidTitleOverride = xxx lorem
	siteName = My site
	email = noreply@xxx.pl
	enableHTMLMail = 0
	enableEmailConfirmation = 1
	enableEmailOnRegister = 1
	enableEmailOnApprove = 0
	useShortUrls = 0
}


