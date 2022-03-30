(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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
		 console.log(user_record);
		 var savedView;
		/**
		 * Ajax to delete booking
		 * @param {Event} e 
		 */
		function delete_booking(e){
			e.preventDefault();
			var studentID = $('div.ad-booking-page').attr('data-student-id');
			var booking_id = $(e.target).attr('data-ad-booking');
			var data = {
				'action' : 'ad_delete_student_booking',
				'student_id': studentID,
				'booking_id': booking_id
			}

			$.post(ajaxurl.ajax_url, data, response => {
				if(response.success){
					$(e.target).closest('tr').remove();
					location.reload();
				}
			})
		}

		$('#exam-booking').live('change', toggle_exam_table);
		/**
		 * Event Handler for Exam view
		 * @param {Event} e 
		 *  
		 */
		function toggle_exam_table(e){
			let exam = $(e.target).prop('checked');
			let table = $('table#agent-availability');
			if(!table.hasClass('exam-view')){
				savedView = table.clone(true);
				table.find('.time-slot').removeClass('block');
				table.addClass('exam-view')
			}else{
				table.fadeOut(100, function(){
					table.replaceWith(savedView);
					table.fadeIn(100);
				})
				
			}
		}

		/**
		 * Ajax to get agent schedule
		 * @param {Object} data
		 */
		function get_agent_schedule( data, counter ){
			var agentTable = $('table#agent-availability');
			$.post(ajaxurl.ajax_url, data, response => {
				if(response.success){
					var times = ['08:00', '08:15', '08:30', '08:45', '09:00', '09:15', '09:30', '09:45', '10:00', '10:15', '10:30', '10:45', '11:00', '11:15', '11:30', '11:45', '12:00', '12:15', '12:30', '12:45', '13:00', '13:15', '13:30', '13:45', '14:00', '14:15', '14:30', '14:45', '15:00', '15:15', '15:30', '15:45', '16:00', '16:15', '16:30', '16:45', '17:00', '17:15', '17:30', '17:45', '18:00', '18:15', '18:30', '18:45', '19:00', '19:15', '19:30', '19:45', '20:00'];
					var headerData;
					var tableHead = agentTable.find('thead').find('tr');
					var tableBody = agentTable.find('tbody');
					if(!tableHead.is(':empty')) tableHead.empty();
					if(!tableBody.is(':empty')) tableBody.empty();
					tableHead.append('<th></th>');
					console.log(response.dates);
					var datesData;
					for (let index = 0; index < times.length; index++) {
						const element = times[index];
						tableHead.append(`<th>${times[index]}</th>`);
						
					}
					for(const date in response.dates){
						//Generate days rows
						var day = moment(date, 'YYYY-MM-DD').format('dddd');
						var bodyRow = $(`<tr id="${day}" data-date="${date}"></tr>`);
						bodyRow.append(`<td class="row-date">${day} <span>${date}</span></td>`);
						
						tableBody.append(bodyRow);

						for (let index = 0; index < times.length; index++) {
							const element = times[index];
							let hour = element.split(':')[0];
							let minutes = element.split(':')[1];
							
							if(response.dates[date][hour]){
								console.log(hour);
								if(response.dates[date][hour].length){
									if(response.dates[date][hour].includes(minutes)||response.dates[date][hour].includes(minutes)){
										var td = $(`<td class="time-slot active" data-slot="${hour}:${minutes}"></td>`);
										td.on('click', time_slot_clicked);
									}else{
										var td = $(`<td class="time-slot active block" data-slot="${hour}:${minutes}"></td>`);
									}
								}else{
									var td = $(`<td class="time-slot active block" data-slot="${hour}:${minutes}"></td>`);
								}

							}else{
								var td = $(`<td class="time-slot active block" data-slot="${hour}:${minutes}"></td>`);

							}
							bodyRow.append(td);

						}
					}

				}else{
					$('div.booking-confirmation').html(`<span>No Agent Available</span> <span> Please give us a call to assign one to you</span>`).addClass('fail');
				}
			})
		}

		/**
		 * Event listenere for time slots
		 */
		function time_slot_clicked(e){
			var exam = $('#exam-booking').prop('checked');
			var remove = $(e.target).hasClass('selected');
			$(e.target).closest('tr').find('td.time-slot.selected').each(function(){
				$(this).removeClass('selected');
			})
			$(e.target).closest('tr').siblings().find('td.time-slot.selected').each(function(){
				$(this).removeClass('selected');
			})
			var end;
			if(exam) {
			 end = 6;
			}else{
				end = user_record.booking_counter == '' ? 2 : user_record.booking_counter * 2 ;
			}
			var slot = $(e.target).nextAll('td').andSelf().slice(0, end--);
			var checkthis = slot.hasClass('block');
			if(!remove){
				if(!slot.hasClass('block')){
					slot.toggleClass('selected');
					var value = `${$(e.target).closest('tr').attr('data-date')} ${$(e.target).attr('data-slot')} to ${$(e.target).closest('tr').attr('data-date')} ${slot.last().next().attr('data-slot')}`;
					$('#booking-date').val(value);
				}
			}else{
				$('#booking-date').val();
			}

		}

		$('table.wp-list-table a').on('click', delete_booking);
		
		var bookingInput = $('div.add-bookingdate');
		if($('#datetimepicker1').length > 0){
			setTimeout(() => {
				$('#datetimepicker1').datetimepicker();
			}, 2000);
		}

		var showAddBtn = $('#show-booking');
		showAddBtn.on('click', (e)=>{
			e.preventDefault();
			// e.preventPropagation();
			bookingInput.toggleClass('show');
			$('td.time-slot').each(function(){
				$(this).addClass('active');
			})
			/**
			 * Event handler for selected time slot
			 */
			$('#exam-booking').on('change', function(){
				if(!this.checked){
					$('#agent-availability > tbody').find('td.time-slot.selected').each(function(){
						$(this).removeClass('selected');
					})
				}
			})
			// $('td.time-slot').on('click', time_slot_clicked )
			
		});

		var submitAddBookingBtn = $('button#submit-student-booking');

		submitAddBookingBtn.on('click', e => {
			var exam = $('#exam-booking').prop('checked');
			var studentID = $('div.ad-booking-page').attr('data-student-id');
			var bookingDateInput = $('#booking-date');

			if(bookingDateInput.val() !== ''){
				// console.log(bookingDateInput.val());

				var data = {
					'action' : 'ad_add_student_booking',
					'student_id' : studentID,
					'booking_date' : bookingDateInput.val(),
					'exam_booking' : exam
				}

				$.post(ajaxurl.ajax_url, data, response => {
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
							row.append('<td>' + response.booking.booking_date + '</td>');
							let deleteTag = $('<a href="#" data-ad-booking="' + response.booking.job_id + '">Cancel</a>');
							let status = $('<td>Pending | </td>');
							status.append(deleteTag);
							row.append(status);
							deleteTag.on('click', delete_booking);

							//Show new booking as blocked
							$('table#agent-availability tbody').find('td.selected').removeClass('selected').addClass('block').off();

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
		/**
		 * Initialize datepicker with values
		 */
		var datepicker = $('#date-picker > input');
		var today = moment().format('YYYY-MM-DD');
		var toDate = moment().add(6, 'day').format('YYYY-MM-DD');
		datepicker.val(`${today} to ${toDate}`);

		/**
		 * Initialize availibilty table with one week data
		 */
		if($('div.ad-booking-page').length){
			get_agent_schedule( { action : 'ad_get_agent_schedule', date_range : `${today} to ${toDate}`}, 7 );
		}

		if($('#date-picker > input').length){
			/**
			 * Agent schedule data
			 */
			$('#date-picker > input').dateRangePicker({
				maxDays: 7,
				minDays: 3,
				startDate: today
			})
			.bind('datepicker-change',function(event,obj){
				/* This event will be triggered when second date is selected */
				// obj will be something like this:
				// {
				// 		date1: (Date object of the earlier date),
				// 		date2: (Date object of the later date),
				//	 	value: "2013-06-05 to 2013-06-07"
				// }
				let from = moment(obj.date1);
				let end = moment(obj.date2);
				get_agent_schedule( { action : 'ad_get_agent_schedule', date_range : obj.value}, end.diff(from, 'days')+1 );
			})
		}
		
	 });




})( jQuery );
