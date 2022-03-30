(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	 $(document).ready(function(){

		/**
		 * Delete booking
		 */
		function delete_booking(e){
			console.log(e.target);
			e.preventDefault();
			var booking_id = $(e.target).attr('data-ad-booking');
			if(!window.studentDetail){
				window.studentDetail = {}
				window.studentDetail.ID = $(e.target).attr('data-ad-student');
			}
			var data = {
				'action' : 'ad_delete_student_booking',
				'student_id': window.studentDetail.ID,
				'booking_id': booking_id
			}

			$.post(ajaxurl, data, response => {
				if(response.success){
					$(e.target).closest('tr').remove();
				}
			})
		}

		/**
		 * Change agent for student show form
		 */
		function ChangeStudentAgent(e){
			e.preventDefault();
			var id = $(e.target).attr('data-id');
			var agent = $(e.target).attr('data-agent');
			var wrapper = $(e.target).parent();
			var data = {
				'action': 'ad_get_all_agents_name_id'
			};
			var agents = [];
			$.post(ajaxurl, data, response => {
				if(response.success){
					var selEl = $('<select name="agents" id="agents_select"></select>')
					console.log(response.data);
					response.data.forEach(ag => {
						if(ag.inst_name !== agent)
							selEl.append(`<option value=${ag.instructor_id}>${ag.inst_name}</option>`);
					});
					wrapper.html(selEl);
					var updateBtn = $(`<a href="#" data-id=${id}>Update</a>`);
					updateBtn.on('click', updateStudentAgent)
					wrapper.append(updateBtn);
				}
			})
		}

		/**
		 * Update student Agent
		 */
		function updateStudentAgent(e){
			e.preventDefault();
			var id = $(e.target).attr('data-id');
			var agentID = $('#agents_select').val();

			var data = {
				'action' : 'ad_update_student_agent',
				'uid': id,
				'agentID' : agentID
			}

			$.post(ajaxurl, data, response => {
				if(response.success){
					alert('Agent Updated, reloading...');
					setTimeout(()=>{
						location.reload();
					}, 800)
					
				}
			})
		}

		$('tbody#the-list a.delete-booking').on('click', delete_booking);
		// var studentDetail;
		if($('#datetimepicker1').length > 0){
			$('#datetimepicker1').datetimepicker();
		}
		var student_container = $('.ad-student-details');
		var student_card = $('#student-card');
		var fields = {
			billing_address_1 : 'Address',
			billing_city : 'City',
			billing_phone : 'Phone',
			billing_postcode : 'Postal Code',
			billing_state : 'Province',
			first_name : 'First Name',
			last_name : 'Last Name',
			student_dob : 'Date of Birth',
			student_g2el : 'G2 Eligibilty Date',
			student_lcissue : 'License Issue Date',
			student_license : 'License Number',
			agent_name : 'Instructor'
		}

		/**
		 * Ajax Generate ZCRM TOKEN
		 */
		var ztoken_btn = $('#generate_token');

		ztoken_btn.on('click', e => {
			e.preventDefault();

			var data = {
				'action': 'generate_zcrm_token'
			}

			$.post(ajaxurl, data, response => {
				if(response){
					$('.zcrm_token_status').text(response.message);
				}
			});
		});

		var STDEditBtn = $('.ad-students-content .edit-student a');
		var table = $('tbody.the-list');

		STDEditBtn.on('click', e => {
			e.preventDefault();
			// console.log($(e.target));
			//:: TODO get data
			var id = $(e.target).attr('data-ad-student');
			var data = {
				'action' : 'ad_get_student_details',
				'student_id' : id
			}

			$.post(ajaxurl, data, response => {
				if(response.success){
					// console.log(response.data);
					window.studentDetail = response.data.data;
					window.studentDetail.ID = id;
					var template = $('<div class="student-card-content"></div>');
					var empty = $('#empty-bookings');
					for(var key in response.data.data){
						if( key in fields){
							if(key == 'agent_name'){
								var el = $(`<div> <span class="label">${fields[key]}:</span> <span id="agent_data">${response.data.data[key]}</span> `);
								var changeBtn = $(`<a href="" data-agent="${response.data.data[key]}" data-id=${window.studentDetail.ID}>Change</a>`);
								changeBtn.on('click', ChangeStudentAgent);
								el.find('#agent_data').append(changeBtn);
								template.append(el);
							}else{
								template.append(`<div> <span class="label">${fields[key]}:</span> ${response.data.data[key]}`);
							}
						}
					};
					if(response.data.bookings){
						$('#empty-bookings').parent().remove();
						response.data.bookings.forEach(element => {
							let row = $('<tr></tr>');
							console.log(table);
							row.append('<td>' + element.instructor + '</td>');
							row.append('<td>' + element.booking_date + '</td>');
							let status = element.status == '1' ? 'Pending' : 'Completed';
							var statusCell = $('<td>' + status + ' </td>');
							row.append(statusCell);

							var deleteTag = $('| <a href="#" data-ad-booking="' + element.job_id + '" class="delete-booking">Cancel</a>');
							if(status == 'Pending'){
								statusCell.append(deleteTag);
							}
							deleteTag.on('click', delete_booking);
							row.append(statusCell);

							table.append(row);
						});
					}
					student_container.addClass('show');

					// var table = $('<table class="student-card-bookings"></table>');
					
					student_card.prepend(template);

				}
			})
		});

		var cardCloseBtn = $('a#close-student-card');

		cardCloseBtn.on('click', e => {
			e.preventDefault();
			var empty = $('<tr><td colspan="3" id="empty-bookings">No Bookings</td><tr>');
			$('div.student-card-content').remove();
			table.find('tr').remove();
			table.append(empty);
			student_container.removeClass('show');

		});

		var addBookingBtn = $('div.add-booking');
		var addBookingInput = $('div.add-bookingdate');
		addBookingBtn.on('click', e => { addBookingInput.toggle() });


		var submitAddBookingBtn = $('button#submit-student-booking');

		submitAddBookingBtn.on('click', e => {
			var bookingDateInput = $('#booking-date');

			if(bookingDateInput.val() !== ''){
				console.log(studentDetail);

				var data = {
					'action' : 'ad_add_student_booking',
					'student_id' : studentDetail.ID,
					'booking_date' : bookingDateInput.val()
				}
				console.log(data);

				$.post(ajaxurl, data, response => {
					console.log(response);
					if(response.success){
						// console.log(response.data);
						if(response.message == 'Booking Confirmed and Saved!'){
							bookingDateInput.val('');
							console.log($('#empty-bookings').parent());
							$('#empty-bookings').parent().remove();
							var row = $('<tr></tr>');
							var table = $('.the-list');
							console.log(table);
							row.append('<td>' + response.booking.agent_id + '</td>');
							row.append('<td>' + response.booking.booking_date + '</td>');
							let deleteTag = $('| <a href="#" data-ad-booking="' + response.booking.job_id + '" class="delete-booking>Cancel</a>');
							let status = $('<td>Pending </td>');
							status.append(deleteTag);
							row.append(status);
							deleteTag.on('click', delete_booking);

							table.append(row);

						}
						setTimeout(() => {
							$('div.booking-confirmation').text(response.message).addClass('success');
						}, 3000);

					}else{
						setTimeout(() => {
							$('div.booking-confirmation').html(`<span>${response.message}</span> <span> Please give us a call to book</span>`).addClass('fail');
						}, 6000);
					}
				})
			}

		});

		var showBookingsBtn = $('#view-bookings');
		showBookingsBtn.on('click', e => {
			console.log($(e.target));
			console.log($(e.target).next('.instructor-bookings'));
			$(e.target).closest('tr').next('tr').find('.instructor-bookings').toggleClass('show');
		});

		/**
		 * Delete user
		 */
		var deleteUserBtn = $('a.submitdelete');
		deleteUserBtn.on('click', e => {
			e.preventDefault();
			var data = {
				'action' : 'ad_delete_student',
				'student_id' : $(e.target).attr('data-ad-user'),
				'wpnonce' : $(e.target).attr('data-ad-nonce')
			}
			$.post(ajaxurl, data, response => {
				if(response){
					if(response.success){
						alert('User Deleted, Reloading in 5s...');
						setTimeout(() => {
							location.reload();
						}, 2000);
					}else{
						alert('Failed to delete user...')
					}
				}
			})
		});

		var refreshAgentsBtn = $('button#get_agents');
		refreshAgentsBtn.on('click', e => {
			e.preventDefault();

			var data = {
				'action' : 'ad_get_agents'
			}

			$.post(ajaxurl, data, response => {
				if(response.success){
					location.reload();
					$('.ad-get-agents').append(`<pre>${JSON.stringify(JSON.parse(response.message), null, 2)}</pre>`)
					console.log(JSON.parse(response.message));
				}
			})
		});

	 })

	 

})( jQuery );
