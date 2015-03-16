plugin.Tx_Formhandler.settings.predef.formhandler-basic-contactform {

  name = Contact Form
  
  formValuesPrefix = contact_form

  langFile.1 = TEXT
  langFile.1.value = {$formhandlerExamples.basic.contact-form.rootPath}/lang/lang.xml

  templateFile = TEXT
  templateFile.value = {$formhandlerExamples.basic.contact-form.rootPath}/html/step-1.html

  masterTemplateFile = TEXT
  masterTemplateFile.value = {$formhandlerExamples.basic.contact-form.rootPath}/html/mastertemplate.html

  cssFile {
    10 = TEXT
    10.value = {$formhandlerExamples.basic.contact-form.rootPath}/skin/css/base.css
    20 = TEXT
    20.value = {$formhandlerExamples.basic.contact-form.rootPath}/skin/css/forms.css
    25 = TEXT
    25.value = {$formhandlerExamples.basic.contact-form.rootPath}/skin/css/special.css
    30 = TEXT
    30.value = {$formhandlerExamples.basic.contact-form.rootPath}/skin/css/colors.css
  }

  singleErrorTemplate {
    totalWrap = <div class="error">|</div>
    singleWrap = <span class="message">|</span>
  }

  validators {
    1.class = Validator_Default
    1.config.fieldConf {
      name.errorCheck.1 = required
						surname.errorCheck.1 = required
						company.errorCheck.1 = required
						street.errorCheck.1 = required
						number.errorCheck.1 = required
						postcode.errorCheck.1 = required      
						country.errorCheck.1 = required
						email.errorCheck.1 = required
      email.errorCheck.2 = email
    }
  }

  finishers {

    1.class = Finisher_Mail
    1.config {
      checkBinaryCrLf = message
      admin {
        templateFile = TEXT
        templateFile.value = {$formhandlerExamples.basic.contact-form.rootPath}/html/email-admin.html
        sender_email = {$formhandlerExamples.basic.contact-form.email.admin.sender_email}
        to_email = {$formhandlerExamples.basic.contact-form.email.admin.to_email}
        subject = TEXT
        subject.data = LLL:{$formhandlerExamples.basic.contact-form.rootPath}/lang/lang.xml:email_admin_subject
      }
    }
    
    2 {       
      class = Tx_Formhandler_Finisher_SubmittedOK
      
      # exit - no other finishers
      config.returns = 1     
    }  

    # Finisher_Redirect will redirect the user to another page after the form was submitted successfully.
    5.class = Finisher_Redirect
    5.config {
      redirectPage = {$formhandlerExamples.basic.contact-form.redirectPage}
    }
  }

}

# If the user has chosen to receive a copy of the contact request, reconfigure Finisher_Mail to send an email to the user to.
#[globalVar = GP:contact|receive-copy = 1]
#plugin.Tx_Formhandler.settings.predef.formhandler-basic-contactform {
#  finishers {
#    1.config {
#      user {
#        templateFile = TEXT
#        templateFile.value = {$formhandlerExamples.basic.contact-form.rootPath}/html/email-user.html
#        sender_email = {$formhandlerExamples.basic.contact-form.email.user.sender_email}
#        to_email = email
#        subject = TEXT
#        subject.data = LLL:{$formhandlerExamples.basic.contact-form.rootPath}/lang/lang.xml:email_user_subject
#      }
#    }
#  }
#}
#[global]



plugin.Tx_Formhandler.settings.predef.formhandler-callback-service {

  name = Call-back Service
  
  formValuesPrefix = cb_service

  langFile.1 = TEXT
  langFile.1.value = {$formhandlerExamples.basic.contact-form.rootPath}/lang/lang.xml

  templateFile = TEXT
  templateFile.value = {$formhandlerExamples.basic.contact-form.rootPath}/html/step-1.html

  masterTemplateFile = TEXT
  masterTemplateFile.value = {$formhandlerExamples.basic.contact-form.rootPath}/html/mastertemplate.html

  cssFile {
    10 = TEXT
    10.value = {$formhandlerExamples.basic.contact-form.rootPath}/skin/css/base.css
    20 = TEXT
    20.value = {$formhandlerExamples.basic.contact-form.rootPath}/skin/css/forms.css
    25 = TEXT
    25.value = {$formhandlerExamples.basic.contact-form.rootPath}/skin/css/special.css
    30 = TEXT
    30.value = {$formhandlerExamples.basic.contact-form.rootPath}/skin/css/colors.css
  }

  singleErrorTemplate {
    totalWrap = <div class="error">|</div>
    singleWrap = <span class="message">|</span>
  }

  validators {
    1.class = Validator_Default
    1.config.fieldConf {
      name.errorCheck.1 = required
						#surname.errorCheck.1 = required
						#company.errorCheck.1 = required
						#street.errorCheck.1 = required
						#number.errorCheck.1 = required
						#postcode.errorCheck.1 = required      
						#country.errorCheck.1 = required
						phone.errorCheck.1 = required
						reason.errorCheck.1 = required
    }
  }

  finishers {

    1.class = Finisher_Mail
    1.config {
      checkBinaryCrLf = message
      admin {
        templateFile = TEXT
        templateFile.value = {$formhandlerExamples.basic.contact-form.rootPath}/html/email-admin.html
        sender_email = {$formhandlerExamples.basic.contact-form.email.admin.sender_email}
        to_email = {$formhandlerExamples.basic.contact-form.email.admin.to_email}
        subject = TEXT
        subject.data = LLL:{$formhandlerExamples.basic.contact-form.rootPath}/lang/lang.xml:email_admin_subject
      }
    }

    # Finisher_Redirect will redirect the user to another page after the form was submitted successfully.
    5.class = Finisher_Redirect
    5.config {
      redirectPage = {$formhandlerExamples.basic.contact-form.redirectPage}
    }
  }

}

# If the user has chosen to receive a copy of the contact request, reconfigure Finisher_Mail to send an email to the user to.
#[globalVar = GP:contact|receive-copy = 1]
#plugin.Tx_Formhandler.settings.predef.formhandler-callback-service {
#  finishers {
#    1.config {
#      user {
#        templateFile = TEXT
#        templateFile.value = {$formhandlerExamples.basic.contact-form.rootPath}/html/email-user.html
#        sender_email = {$formhandlerExamples.basic.contact-form.email.user.sender_email}
#        to_email = email
#        subject = TEXT
#        subject.data = LLL:{$formhandlerExamples.basic.contact-form.rootPath}/lang/lang.xml:email_user_subject
#      }
#    }
#  }
#}
#[global]
