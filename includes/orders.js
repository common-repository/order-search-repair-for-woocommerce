///
//
//	VNM Order Search: JS functionality
//	v0.1
//	Author: Lawrie Malen
//	Date: 09/05/2017
//
///

jQuery(document).ready(function($) {
	
	var errorTimeout;
	var _form = $('#vnmadmin.vnmordersearch');
	var formAction = $('input#action').val();
	var formBlocker = $('#vnm-form-blocker');
	
	_form.find('.errormessage').hide();
	
	var _button = _form.find('.ajax-send');
	
	var cancelWrapper = _form.find('.submit.cancel');
	var cancelButton = cancelWrapper.find('.ajax-abort');
	
	var emptyError = _form.find('.empty-error-message');
	var timeoutError = _form.find('.timeout-message');
	
	var updateBlock = $('#vnm-ordersearch-progress');
	var updatePre = updateBlock.attr('data-prefix');
	var updatePost = updateBlock.attr('data-postfix');
	var updateDone = updateBlock.attr('data-done');
	
	var xhr;
	
	$('#vnm-ordersearch-sql').hide();
	
	/////
	///
	//	FORM SUBMISSION
	///
	/////
	
	_form.on('click', '.ajax-send', function(e) {
		e.preventDefault();
		
		_button = $(this);
		_form.find('.errormessage').slideUp();
		
		var sendData = {};
		
		if (_form.find('input#sql').prop('checked') == false && _form.find('input#modify').prop('checked') == false) {
			emptyError.slideDown();
			return;
		}
		
		errorTimeout = window.setTimeout(function() {
			
			var errorFound = false;
			
			_form.find('input').each(function(index) {
				
				var fieldName = $(this).attr('data-name');
				console.log(fieldName + ': ' + $(this).val());
				
				if ($(this).val() != '') {
					sendData[fieldName] = $(this).val();
				} else {
					if ($(this).prop('required')) {
						emptyError.slideDown();
						errorFound = true;
					}
				}
				
				if ($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio') {
					delete sendData[fieldName];
					
					if ($(this).prop('checked')) {
						sendData[fieldName] = true;
					}
				}
				
			});
			
			if (errorFound) {
				return;
			}
			
			//	Everything looks good - let's-a go!
			
			activateForm(formBlocker, false);
			
			showUpdate(0, false);
			
			delegateAjaxSend(sendData);
			
		}, 150);
		
		return false;
	});
	
	///
	//	Cancel ajax 
	///
	
	_form.on('click', '.ajax-abort', function(e) {
		e.preventDefault();
		
		xhr.abort();
		activateForm(formBlocker, true);
	});
	
	///
	//	Delegated ajax send function
	///
	
	function delegateAjaxSend(sendData) {
		
		sendData['action'] = formAction;
		
		console.log(sendData);
		
		//	Try sending the ajax request
		
		Promise.resolve(
			xhr = $.ajax({
				url: ajaxurl,
				type: 'POST',
				data: sendData,
				dataType: 'json',
				timeout: 60000
			})
		).then(function(data) {
			if (data.response == 'success') {
				$('body').trigger('vnm_ordersearch_success', [data]);
			} else {
				$('body').trigger('vnm_ordersearch_failed', [data]);
			}
		}).catch(function(e) {
			console.alert(e);
			if (e.statusText == 'timeout') {
				$('body').trigger('vnm_ordersearch_error_response', [e]);
				timeoutError.slideDown();
			}
		});
	}
	
	///
	//	Success! Renew the chunk!
	///
	
	$(document.body).on('vnm_ordersearch_success', function(evt, data) {
		
		showUpdate(data.newoffset, false);
		
		var sendData = {};
		
		if (data.sql) {
			if (!$('#vnm-ordersearch-sql').is(':visible')) {
				$('#vnm-ordersearch-sql').slideDown();
			}
			
			$('#vnm-ordersearch-sql').append(data.sql);
			sendData['sql'] = true;
		}
		
		if (data.modify && data.modify == 'true') {
			sendData['modify'] = true;
		}
		
		if (data.status == 'completed') {
			showUpdate(data.newoffset, true);
			activateForm(formBlocker, true);
			
			return;
		}
		
		sendData['loop'] = true;
		sendData['limit'] = parseInt(data.limit);
		sendData['offset'] = parseInt(data.newoffset);
		
		delegateAjaxSend(sendData);
	});
	
	///
	//	Re-activate the form after completion or cancellation
	///
	
	function activateForm(blocker, bool) {
		
		_actionButton = blocker.closest('.form').find('button');
		
		if (bool) {
			blocker.removeClass('active');
			_actionButton.prop('disabled', false);
		} else {
			blocker.addClass('active');
			_actionButton.prop('disabled', true);
		}
	}
	
	///
	//	Error!
	///
	
	$(document.body).on('vnm_ordersearch_error_response', function(evt, data) {
		console.log(data);
	});
	
	///
	//	Show update
	///
	
	function showUpdate(num, done) {
		var showNum = '<strong>' + num + '</strong>';
		var _prefix = updatePre;
		
		if (done) {
			_prefix = updateDone;
		}
		
		updateBlock.html(_prefix + '&nbsp;' + showNum + updatePost);
	}
	
	/////
	///
	//	Retrieve the total number of unsearchable orders
	///
	/////
	
	var totalResult = $('#vnm-ordersearch-total');
	var totalResultWorkingMsg = totalResult.attr('data-working');
	var totalResultFoundMsg = totalResult.attr('data-result');
	
	var retrieveBlocker = $('.form.unsearchable-orders .vnm-form-blocker');
	
	$('.form.unsearchable-orders').on('click', '.ajax-retrieve', function(e) {
		e.preventDefault();
		
		totalResult.empty().text(totalResultWorkingMsg);
		
		activateForm(retrieveBlocker, false);
		
		var sendData = {};
		sendData['action'] = $('.form.unsearchable-orders').find('input.action').val();
		
		//	Try sending the ajax request
		
		Promise.resolve(
			xhr = $.ajax({
				url: ajaxurl,
				type: 'POST',
				data: sendData,
				dataType: 'json',
				timeout: 60000
			})
		).then(function(data) {
			activateForm(retrieveBlocker, true);
			
			if (data.response == 'success') {
				
				totalResult.empty().text(totalResultFoundMsg.formatUnicorn({total: data.total}));
				
			} else {
				
			}
		}).catch(function(e) {
			console.alert(e);
			if (e.statusText == 'timeout') {
				$('body').trigger('vnm_ordersearch_error_response', [e]);
				timeoutError.slideDown();
			}
		});
	});
	
});

///
//	JS sprintf function
///

String.prototype.formatUnicorn = String.prototype.formatUnicorn || function () {
	"use strict";
	var str = this.toString();
	if (arguments.length) {
		var t = typeof arguments[0];
		var key;
		var args = ("string" === t || "number" === t) ?
			Array.prototype.slice.call(arguments)
			: arguments[0];
			
		for (key in args) {
			str = str.replace(new RegExp("\\{" + key + "\\}", "gi"), args[key]);
		}
	}

	return str;
};