/**
 * jquery.calendario.js v1.0.0
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * Copyright 2012, Codrops
 * http://www.codrops.com
 */
;
(function($, window, undefined) {
    'use strict';
    $.Calendario = function(options, element) {
        this.$el = $(element);
        this._init(options);
    };
    // the options
    $.Calendario.defaults = {
        /*
		you can also pass:
		month : initialize calendar with this month (1-12). Default is today.
		year : initialize calendar with this year. Default is today.
		caldata : initial data/content for the calendar.
		caldata format:
		{
			'MM-DD-YYYY' : 'HTML Content',
			'MM-DD-YYYY' : 'HTML Content',
			'MM-DD-YYYY' : 'HTML Content'
			...
		}
		*/
        weeks: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        weekabbrs: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        monthabbrs: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        // choose between values in options.weeks or options.weekabbrs
        displayWeekAbbr: false,
        // choose between values in options.months or options.monthabbrs
        displayMonthAbbr: false,
        // left most day in the calendar
        // 0 - Sunday, 1 - Monday, ... , 6 - Saturday
        startIn: 1,
        /*onDayClick: function($el, $content, dateProperties) {
            return false;
        },
        onEventClick: function($eventDetails) {
            return false;
        },
        onCharityEventClick: function($charityEventDetails) {
            return false;
        }*/
    };
    $.Calendario.prototype = {
        _init: function(options) {
            console.log("INIT........");


    
            // options
            this.options = $.extend(true, {}, $.Calendario.defaults, options);
            
            this.today = new Date();
            this.month = (isNaN(this.options.month) || this.options.month == null) ? this.today.getMonth() : this.options.month - 1;
            this.year = (isNaN(this.options.year) || this.options.year == null) ? this.today.getFullYear() : this.options.year;
            this.caldata = this.options.caldata || {};
            this.mydaydata = {};
            this.caldataNum = {};
            this.eventDetailed = {};
            this._generateTemplate();
            this._initEvents();
            $(".footerContainer").hide();
            $('.closeDetail').on('click', function() {
                $('.charityEventDetails').hide();
            });

        },
        _joinCharityDay: function(charitydayid, callback) {
            console.log("_joinCharityDay" + charitydayid);
            var urlstr = "/welldays/mydays/add/" + charitydayid;

            var self = this;
            $.getJSON(urlstr, function(data) {
                var response = data.response;
                console.dir(response);
				var responseHTML = "";
                //var responseHTML = "<div class='ajaxresponse'>" + response.description + "</div>";
                if (response.success) {
                	responseHTML = response.description;
                	var buttonHref = "javascript:cal._removeCharityDay(" + response.wellday.id + ")";
                	var buttonText = "CANCEL REQUEST";
                	$(".charityEventDetails .charityEventContent .butnRespond").attr("href", buttonHref);
                	$(".charityEventDetails .charityEventContent .butnRespond").text(buttonText)
                    //responseHTML += '<br/><a  class="joinCharityDay" href="javascript:cal._removeCharityDay(' + response.wellday.id + ')">REMOVE!!!!!</a>';
                } else {
                    //responseHTML += '<br/><a  class="joinCharityDay" href="javascript:cal._joinCharityDay(\'' + charitydayid + '\')">JOIN</a>';
                    responseHTML = response.description;
                }

                $(".charityEventDetails .charityResponseOverlay").html('<div class="overlayContent"><a href="javascript:void(0);" class="overlayClose">close</a>' + responseHTML + '</div>');
                //NOW NEED TO UPDATE CALENDAR AND CLOSE THIS WINDOW
                //if (response==response+"<br/><a href='' >PUT CANCEL BUTTON HERE</a>"
                //self._showCharityEventDetails(responseHTML, null);
                $('.overlayClose').on('click', function() {
	                $('.overlayContent').hide();
	            }); 
                self.filterData(self.charityid, callback);
            });
        },
        _removeCharityDay: function(mydayid, callback) {
            console.log("removecharityday " + mydayid);
            var urlstr = "/welldays/mydays/delete/" + mydayid;
            var self = this;
            $.getJSON(urlstr, function(data) {
                var response = data.response;
                //NOW NEED TO UPDATE CALENDAR AND CLOSE THIS WINDOW
                self._hideCharityEventDetails();
                self.filterData(self.charityid, callback);
            });
        },
        _showCharityEventDetails: function(htmlstr,pos) {	
        	$(".charityEventDetails .charityResponseOverlay").html("");//we need to move the overlay off the pop up (somehow hide it) -- perhaps add a class
            $(".charityEventDetails .charityEventContent").html(htmlstr);
         	//set the location
         	$('.charityEventDetails').show();

            if (pos) {

               /* var parenttop = $('.mainContentContainer').position().top;
                var navtop = $('.box').position().top;
                var linkPos =  $('.calBlock');
                console.log("parenttop" + parenttop);
                var topstr = (pos.top - parenttop - navtop) + "px";
                var setposleft = pos.left;*/
                /*if (pos.left > $(window).width() - 460) {
                    setposleft -= 460;
                }*/

                var setpostop = pos.top;
                var setposleft = pos.left;
                var topstr = setpostop + "px";
                var leftstr = setposleft + "px";
                $('.charityEventDetails').css({
                    top: topstr,
                    left: leftstr
                })

            }
            //WHY IS THIS HERE?  YOU SHOULDNT HAVE TO ADD THIS EVERY TIME, RIGHT?
            var clicked = true;
            $('.descLink').on('click', function() {
            	$('.charityDetailDescription').show();
                // if (clicked) {
                //     clicked = false;
                //     $('.charityDetailDescription').css({
                //         'bottom': '-89px'
                //     });
                // } else {
                //     clicked = true;
                //     $('.charityDetailDescription').css({
                //         'bottom': '-420px'
                //     });
                // }
            });
            $('.backToDetails').on('click', function(){
            	$('.charityDetailDescription').hide();
            });


            var headerHeight = $('h5.displayBlock').height();
            var partListHeight = $('.participantsList ul').height();
            if (headerHeight > 50){
            	$('.participantsList').height(46);
            }else {
            	$('.participantsList').height(92);
            }
        },
        _expandRow:function(row,num) {
            this._hideCharityEventDetails();
            //num is the maximum number for that particular day
            //need to figure out the current height for this row and the maximum height
            
            var curr4row =  $("#row_"+row).data( "current" );
            var target = Math.min(curr4row+5,num);
            $("#row_"+row).height(140*target);

            if (target==num) {
                //WE ARE AT THE MAX EVENTS FOR THIS DAY -- CHANGE BUTTON

                $("div.fc-loadmore").html('<a href="javascript:cal._contractRow(' + row  + ','+num+')">' + ' LESS EVENTS</a>');
            }
            $("#row_"+row).data( "current",target);

        },

        _contractRow:function(row,num) {
            this._hideCharityEventDetails();
           var default4row= $("#row_"+row).data( "default" );
            console.log("default4row"+default4row);
            $("#row_"+row).height(140*default4row);
            $("div.fc-loadmore").html('<a href="javascript:cal._expandRow(' + row + ','+num+')">' + ' +MORE EVENTS</a>');
            $("#row_"+row).data( "current",default4row);
            
        },







        _loadCharityEventDetails: function(charitydayid, position, ismyDay,myDayStatus) {
            
            //NEED TO SHOW THE BOX FIRST (POSSIBLY ANIMATE IN) AND THEN SHOW A LOADER FOR CONTENT.
            this._showCharityEventDetails("<img class='loading' src='../assets/images/icon-loading.gif' />", position);
            var urlstr = "/welldays/charitydays/get/" + charitydayid;
            console.log("_loadCharityEventDetails"+urlstr);
            var self = this;
            this.charityArray = [];
            if (this.charityEventRequest) {
                this.charityEventRequest.abort();
            }
            
            this.charityEventRequest=$.getJSON(urlstr, function(data) {
                var charities = data.response[0];
                var participants = charities.users;
                var approvedUsers = participants.approved;
                var pendingUsers = participants.pending;
                var participantsList = {};
                //charities.user = {};
                //var userCount = charities.users[approved].length;
                //console.log('hellow', participants);
                console.dir(pendingUsers);
                var totalParticipants = pendingUsers.length + approvedUsers.length;
                var parentList = $('.participantsList');
                var pendingHTML = "<ul>";
                for (var i = 0; i < pendingUsers.length; i++) {
                    pendingHTML += "<li class=pendingUsers>";
                    pendingHTML += "<div class='userImg'>";
                    pendingHTML += "<img src='" + pendingUsers[i].image + "'/>";
                    pendingHTML += "</div>";
                    pendingHTML += "<span>" + pendingUsers[i].name + "</span>";
                    pendingHTML += "</li>";
                }
                for (var i = 0; i < approvedUsers.length; i++) {
                    pendingHTML += "<li class=approvedUsers>";
                    pendingHTML += "<div class='userImg'>";
                    pendingHTML += "<img src='" + approvedUsers[i].image + "'/>";
                    pendingHTML += "</div>";
                    pendingHTML += "<span>" + approvedUsers[i].name + "</span>";
                    pendingHTML += "</li>";
                }
                pendingHTML += "</ul>";
                //count the amount of participants

                //YOU PROBABLY WANT participants.pending and participants.approved
                //YOU ALSO WILL NEED TO PARSE THOSE.  YOU CANT JUST DROP IT IN THE HTML
                var charityDayLat = charities.latitude;
                var charityDayLong = charities.longitude;
                var charityDayLocation = [charityDayLat, charityDayLong];
                //console.log("hai " + charityDayLocation);
                //
                var date = new Date();
                var today = new Date(date.valueOf() + date.getTimezoneOffset() * 60000);
                var submissionEndDate = new Date(charities.submit_date_end.replace(/-/g, "/"));
                var submissionStartDate = new Date(charities.submit_date_start.replace(/-/g, "/"));
                console.dir(submissionEndDate);
                console.dir(submissionStartDate);
                console.dir(today);
                var submissionDatePass = (submissionEndDate >= today) && (submissionStartDate <= today)
                console.log ("---------------submissionDatePass"+submissionDatePass);
                //CHECK IF SUBMISSION END DATE IS LESS THAN TODAY OR SUBMISSION START DATE IS AFTER TODAY.  IF SO EDIT THE HTML STRING TO SAY THAT.
                //charities.submit_date_start and charities.submit_date_end
                var dateStart = new Date(charities.date.replace(/-/g, "/"));
                var dateEnd = new Date(charities.date_end.replace(/-/g, "/"));
                console.log("!!!!!!"+charities.date);
                var duration = Math.abs(dateEnd.getTime() - dateStart.getTime()) / 3600000;
                var fullTime = parseInt(duration, 10);
                //var fullTime = Math.floor(time / 60);
                var hoursStart = dateStart.getHours(); //returns 0-23
                var minsStart = dateStart.getMinutes(); //returns 0-59


				var charityDayDate = moment(charities.date).format('dddd, MMMM Do YYYY');
				var charityHourStart = moment(charities.date).format('h:mm A');
				var charityHourEnd = moment(charities.date_end).format('h:mm A');


                //
                var htmlstr = "";

                if (!ismyDay) {
                	var submissionValidate = submissionDatePass ? '<a  class="joinCharityDay butnSpecial" href="javascript:cal._joinCharityDay(\'' + charities.id + '\')">JOIN &gt;</a>' : "<span class= 'submissionError'> Submission date has passed.</span>";
                    
                	var participantsBlock= $(pendingHTML).text().length ? '<div class="participants">' +  '<span>Participants</span><a  class="joinCharityDay butnSpecial" href="javascript:cal._joinCharityDay(\'' + charities.id + '\')">JOIN &gt;</a>' + '<div class="participantsList">' + pendingHTML + '</div>' + '</div>' : '<div class="participants">' +  '<span>No Participants</span>'  + submissionValidate +'</div>';
                	htmlstr = '<div class="charityDayContent">' + '<div class="eventInfo">' + '<span class="displayBlock">' + charities.charityname + '</span>' + '<h5 class="displayBlock">' + charities.title + '</h5>' + '<p>' + charityDayDate + '<br/>' + charityHourStart + '-' + charityHourEnd +  '<span>(' + fullTime + ' hours)</span></p>' + '<a class="charityDayLocation butnSpecial displayBlock" href="https://www.google.com/maps/place/' + charityDayLocation + '"target="_blank">' + '<span class="truncateLocation displayBlock">' + charities.locationname + '</span>' + '</a>' + '</div>'
                	+ participantsBlock + '<a class="descLink displayBlock" href="javascript:void(0)">Stuff you should read</a>' + '<div class="charityDetailDescription"><div class="descHeader"><a class="descLink backToDetails" href="javascript:void(0)">&lt;</a><h5>DESCRIPTION</h5></div>' + charities.description + '</div></div>';
                } else {
                	//Check if ismyDay is "pending" or "approved" and change the HTML accordingly
                	var cancelStr= (myDayStatus == "pending") ? "CANCEL REQUEST": "I CAN'T ATTEND ANYMORE";
                	var participantsBlock = '<div class="participants">' + '<span>Participants</span><a  class="joinCharityDay butnSpecial butnRespond" href="javascript:cal._removeCharityDay(' + ismyDay + ')">'+cancelStr+'</a>' + '<div class="participantsList">' + pendingHTML + '</div>' + '</div>';
                	//var approvedStr = (ismyDay) == "approved"
                    htmlstr = '<div class="charityDayContent">' + '<div class="eventInfo">' + '<span class="displayBlock small">' + charities.charityname + '</span>' + '<h5 class="displayBlock">' + charities.title + '</h5>' + '<p>' + charityDayDate + '<br/>' + charityHourStart + '-' + charityHourEnd + '<span>(' + fullTime + ' hours)</span></p>' + '<a class="charityDayLocation butnSpecial displayBlock" href="https://www.google.com/maps/place/' + charityDayLocation + '"target="_blank">' + '<span class="truncateLocation displayBlock">' + charities.locationname + '</span>' + '</a>' + '</div>' + participantsBlock + '<a class="descLink displayBlock" href="javascript:void(0)">Stuff you should read</a>' + '<div class="charityDetailDescription"><div class="descHeader"><a class="descLink backToDetails" href="javascript:void(0)">&lt;</a><h5>DESCRIPTION</h5></div>' + charities.description + '</div></div>';
                }




                //self._joinCharityDay();
                self._showCharityEventDetails(htmlstr, position);
            });
        },
        _hideCharityEventDetails: function() {
            $('.charityEventDetails').hide();
        },
        _initEvents: function() {
            var self = this;
            /* this.$el.on('click.calendario', 'div.fc-row > div', function() {
             	console.dir(this);
                 var $cell = $(this),
                     idx = $cell.index(),
                     $content = $cell.children('div'),
                     dateProp = {
                         day: $cell.children('span.fc-date').text(),
                         month: self.month + 1,
                         monthname: self.options.displayMonthAbbr ? self.options.monthabbrs[self.month] : self.options.months[self.month],
                         year: self.year,
                         weekday: idx + self.options.startIn,
                         weekdayname: self.options.weeks[idx + self.options.startIn]
                     };
                                                     	//console.log('jai' + dateProp);
                 //$('.charityEventDetails').show();
                 
                 if (dateProp.day) {
                     self.options.onDayClick($cell, $content, dateProp);
                                 	//console.log('jai' + day);
                 }
             });*/
            this.$el.on('click.calendario', 'div.calBlock', function(e) {
                var $cell = $(this),
                    idx = $cell.index(),
                    $content = $cell.children('div'),
                    dateProp = {
                        day: $cell.children('span.fc-date').text(),
                        month: self.month + 1,
                        monthname: self.options.displayMonthAbbr ? self.options.monthabbrs[self.month] : self.options.months[self.month],
                        year: self.year,
                        weekday: idx + self.options.startIn,
                        weekdayname: self.options.weeks[idx + self.options.startIn]
                    };

                var postop = e.pageY;
                console.log(":::::e.pageX >>>> "+e.pageX);

                //console.log("this"+this);
                //console.dir($(this).parent());
                //console.dir(this.parentNode.parentNode);

                //GET LEFT POSITION OF DAY:
                var clickedday=this.parentNode.parentNode;
                window.clickedday=clickedday;
                console.log("clickedday");
                console.dir(clickedday);
                var pos=$(clickedday).position();
                console.log("here is the position of the clicked day");
                console.dir(pos);
                //GET TOP POSITION OF ROW:
                var clickedrow=clickedday.parentNode;
                console.dir(clickedrow);
                var rowpos=$(clickedrow).position();
                var columnwidth=$(clickedday).width();

                window.clickedrow=clickedrow;
                var rowleft=$(clickedrow).css('margin-left').split('px')[0];
                console.log("rowleft "+rowleft);
                console.log("fc-calendar left "+$('.fc-calendar').position().left);
                console.log("columnwidth "+columnwidth);
                pos.top=postop-730;
                //pos.top=rowpos.top;
                pos.left=Number(pos.left)+Number(rowleft)+$('.fc-calendar').position().left;
                
                //ADD COLUMN WIDTH
                pos.left+=columnwidth;

                //CHECK IF WE SHOULD MOVE TO RIGHT OR LEFT
                //IF ON RIGHT, LEAVE AS IS
                if (pos.left+$('.charityEventDetails').width() > $(window).width()) {
                	$('.charityEventDetails').addClass('rightPointer');
                    //UPDATE THE CLASS TO HAVE THE ARROW ON THE RIGHT
                    pos.left -= $('.charityEventDetails').width()+columnwidth+40+8;//fix this later;
                } else {
                	$('.charityEventDetails').removeClass('rightPointer').addClass('leftPointer');

                }
                //IF ON LEFT, SUBTRACT THE WIDTH OF THE POP UP
               // window.clickedday=clickedday;
                //window.clickedrow=clickedrow;
                self._loadCharityEventDetails($(this).data("id"), pos, $(this).data("myday"),$(this).data("mydaystatus"));
                /*if (dateProp.day) {
                    self.options.onDayClick($cell, $content, dateProp);
                }*/
            });
        },
        // Calendar logic based on http://jszen.blogspot.pt/2007/03/how-to-build-simple-calendar-with.html
        _generateTemplate: function(callback) {
            var body = this._getBody(),
                rowClass;
            switch (this.rowTotal) {
                case 4:
                    rowClass = 'fc-four-rows';
                    break;
                case 5:
                    rowClass = 'fc-five-rows';
                    break;
                case 6:
                    rowClass = 'fc-six-rows';
                    break;
            }
            this.$cal = $('<div class="fc-calendar ' + rowClass + '">').append(body);
            
            
            this.$el.find('div.fc-calendar').remove().end().append(this.$cal);
            $('.headRow').html(this._getHead());
            if (callback) {
                callback.call();
            }
        },
        _getHead: function() {
            var html = '<div class="fc-head">';
            for (var i = 0; i <= 4; i++) {
                var pos = i + this.options.startIn,
                    j = pos > 6 ? pos - 6 - 1 : pos;
                html += '<div>';
                html += this.options.weeks[j];
                html += '</div>';
            }
            html += '</div>';
            return html;
        },
        _getBody: function() {
            var d = new Date(this.year, this.month + 1, 0),
                // number of days in the month
                monthLength = d.getDate(),
                firstDay = new Date(this.year, this.month, 1);
            // day of the week
            this.startingDay = firstDay.getDay();
            //var html = '<div class="fc-body"><div class="fc-row">',
            var html = '<div class="fc-body"><div class="headRow"></div><div id= "rowid" class="row-replace" data-default="default-replace" data-current="curr-replace">',
                // fill in the days
                day = 1;
            // this loop is for weeks (rows)
            window.testheight=0;
            for (var i = 0; i < 7; i++) {
                //LET'S FIND OUT WHAT THE MOST EVENTS IN A DAY THIS WEEK IS:
                var maxEventsThisWeek=2;
                var weeklyMyDayCheck=false;
                // this loop is for weekdays (cells)
                for (var j = 0; j <= 4; j++) {
                    //shoudl put a check to see if day is still zero and j is greater than zero.  That means a whole week was added with no days.  should shift row up.
                    var pos = this.startingDay - this.options.startIn,
                        p = pos < 0 ? 6 + pos + 1 : pos,
                        inner = '',
                        today = this.month === this.today.getMonth() && this.year === this.today.getFullYear() && day === this.today.getDate(),
                        content = '';
                    if (day <= monthLength && (i > 0 || j >= p)) {
                        var strdate = this.year + '-' + (this.month + 1 < 10 ? '0' + (this.month + 1) : this.month + 1) + '-' + (day < 10 ? '0' + day : day),
                            dayData = this.caldata[strdate];
                        inner += '<span class="cut"></span><span class="fc-date">' + day + '</span>';
                        var eventsOnThisDate = this.caldataNum[strdate] ? this.caldataNum[strdate] : 0;
                        if (eventsOnThisDate > 5) {
                            inner += '<div class="fc-loadmore"> <a href="javascript:cal._expandRow(' + i + ','+eventsOnThisDate+')">' + ' +MORE EVENTS</a></div>';
                        }
                        
                        // this day is:
                        
                            
                            //console.log(strdate+" "+eventsOnThisDate);
                            maxEventsThisWeek=Math.max(maxEventsThisWeek,eventsOnThisDate )
                        var myDayCheck = this.mydaydata[strdate];
                        if (myDayCheck) {
                            inner += '<div class="myDayContent">' + myDayCheck + '</div>';
                        }
                        //console.log ("myDay???-------------");
                        //console.log (myDayCheck);
                        if (dayData) {
                            content = dayData;
                        }
                        if (content !== '') {
                            inner += '<div class="eventsContent">' + content + '</div>';
                        }
                        ++day;
                    } else {
                        today = false;
                    }
                    var cellClasses = today ? 'fc-currentDay ' : '';
                    if (myDayCheck) {
                        cellClasses += 'fc-myday ';
                        weeklyMyDayCheck=true;
                    }
                    if (content !== '') {
                        cellClasses += 'fc-content';
                    }
                    html += cellClasses !== '' ? '<div class="' + cellClasses + '">' : '<div>';
                    html += inner;
                    html += '</div>';
                }
                var eventsNum = weeklyMyDayCheck ? Math.max(3,Math.min(5,maxEventsThisWeek)) : Math.min(5,maxEventsThisWeek);//dont show more than 5, even if more.

                //html = html.replace("more-replace", eventsNum);
                html = html.replace("row-replace", "fc-row events-"+eventsNum);
                html = html.replace("curr-replace", eventsNum);
                
                html = html.replace("default-replace", eventsNum);
                
                html = html.replace("rowid", "row_"+i);

                /*console.dir($(".fc-row.events-"+eventsNum));
                if ($(".fc-row.events-"+eventsNum)[$(".fc-row.events-"+eventsNum).length-1]) {
                    window.testheight+=$(".fc-row.events-"+eventsNum)[$(".fc-row.events-"+eventsNum).length-1].offsetHeight;
                }*/
                //window.testtop=$(".fc-row.events-"+eventsNum).position().top;
                // stop making rows if we've run out of days
                if (day > monthLength) {
                    this.rowTotal = i + 1;
                    break;
                } else {
                    html += '</div><div class="headRow"></div><div id="rowid" class="row-replace" data-default="default-replace" data-current="curr-replace">';
                }
                day = day + 2;
            }
            html += '</div>';
            html += this._addMonthNav();
            html += this._addFooter();
            html += '</div>';
            //
            return html;
        },
        _addMonthNav: function() {
            var navhtml='<div class="monthFooterNav">';
                navhtml+='<div class="butn solid large monthFooterNavPrev">';
                navhtml+='<a href="javascript:cal.gotoPreviousMonth();" class="largeArrowLeft"><span class="butnArrow leftSide"></span>LAST MONTH</a>';
            	navhtml+='</div>';
                navhtml+='<div class="butn solid large monthFooterNavNext">';
                navhtml+='<a href="javascript:cal.gotoNextMonth();" class="largeArrowRight">NEXT MONTH<span class="butnArrow rightSide"></span></a>';
            	navhtml+='</div>';
                navhtml+='</div> ';
			return navhtml;
        },
        _addFooter: function() {
            var footerhtml='<div class="footerContainer">';
				footerhtml+='<div class="row">';
				footerhtml+='<ul>';
				footerhtml+='<li><a href="http://hr.d5servers.com" title="D5 Kitchen">D5 KITCHEN</a></li>';
				footerhtml+='<li><a href="/welldays/faq/" title="FAQ">FAQ</a></li>';
				footerhtml+='<li><a href="javascript:void(0)" title="CONTACT HR">CONTACT HR</a></li>';
				footerhtml+='</ul>';
				footerhtml+='<span>&COPY; 2015 DROGA</span>';
				footerhtml+='</div>';
				footerhtml+='</div>';

            return footerhtml;
        },
        // based on http://stackoverflow.com/a/8390325/989439
        _isValidDate: function(date) {
            date = date.replace(/-/gi, '');
            var month = parseInt(date.substring(0, 2), 10),
                day = parseInt(date.substring(2, 4), 10),
                year = parseInt(date.substring(4, 8), 10);
            if ((month < 1) || (month > 12)) {
                return false;
            } else if ((day < 1) || (day > 31)) {
                return false;
            } else if (((month == 4) || (month == 6) || (month == 9) || (month == 11)) && (day > 30)) {
                return false;
            } else if ((month == 2) && (((year % 400) == 0) || ((year % 4) == 0)) && ((year % 100) != 0) && (day > 29)) {
                return false;
            } else if ((month == 2) && ((year % 100) == 0) && (day > 29)) {
                return false;
            }
            return {
                day: day,
                month: month,
                year: year
            };
        },
        _move: function(period, dir, callback) {
            if (dir === 'previous') {
                if (period === 'month') {
                    this.year = this.month > 0 ? this.year : --this.year;
                    this.month = this.month > 0 ? --this.month : 11;
                } else if (period === 'year') {
                    this.year = --this.year;
                }
            } else if (dir === 'next') {
                if (period === 'month') {
                    this.year = this.month < 11 ? this.year : ++this.year;
                    this.month = this.month < 11 ? ++this.month : 0;
                } else if (period === 'year') {
                    this.year = ++this.year;
                }
            }
            this._getJSONData(callback);
        },
        _getJSONData: function(callback) {

        	console.log("_getJSONData");
            var urlstr = "/welldays/charitydaysbyDate/" + this.year + "/" + (this.month + 1);
            if (this.charityid) {
                urlstr = "/welldays/charitydaysbyDate/" + this.year + "/" + (this.month + 1) + "?charity=" + this.charityid;
            }
            console.log("hi" + urlstr);
            //LOAD JSON HERE!
            var self = this;
            $.getJSON(urlstr, function(data) {
                
                var dayarray = data.response;
                var calendarEvents = {};
                var calendarEventsNum = {};
                var myDays = {};
                var myDaysToolTip = {};
                var charityEventDetails = {};
                var today = new Date();
                for (var i = 0; i < dayarray.length; i++) {
                    console.log(dayarray[i].date +dayarray[i].myday); 
                    //console.log("day "+dayarray[fi].title);
                    //console.log("date "+dayarray[i].date);
                    //console.dir(dayarray[i]);                   
                    if (dayarray[i].date) {
                        var datestr = dayarray[i].date.split(" ")[0];
                        var dayDate = new Date(datestr.replace(/-/g, "/"));
                        var dateStart = new Date(dayarray[i].date.replace(/-/g, "/"));
                        var dateEnd = new Date(dayarray[i].date_end.replace(/-/g, "/"));
                        var duration = Math.abs(dateEnd.getTime() - dateStart.getTime()) / 3600000;
                        var time = parseInt(duration, 10);
                        var fullTime = Math.floor(time / 60);
                        //var dateStart = dayarray[i].date.split(" ")[1];
                        //var dateEnd = dayarray[i].date_end.split(" ")[1];
                        //var hourStart = new Date(dateStart);
                        //var hours = Math.abs(dateStart - dateEnd) / 36e5;
                        // var charityDayLat = dayarray[i].latitude;
                        // var charityDayLong = dayarray[i].longitude;
                        // var charityDayLocation = [charityDayLat, charityDayLong];
                        var addOpacity = "";
                        var clickDetails = "";
                        //var myDay = (dayarray[i].myday.status==true);
                        if (dayDate < today) {
                            addOpacity = " style='opacity:.5;'"
                        }
                        if (!calendarEvents) {
                            self.options.onCharityEventClick('.calBlock');
                        }
                        
                        if (dayarray[i].myday && dayarray[i].myday.status) {
                            //THIS IS A MYDAY
                            myDays[datestr] = '<div class="calBlock" data-myday="' + dayarray[i].myday.myday_id + '"data-mydaystatus="' + dayarray[i].myday.status + '" data-id="' + dayarray[i].id + '"' + addOpacity + ' ><div class="myDayContentDetails"><span>MY WELL DAY</span>' + '<div class="userImg"><img src="' + dayarray[i].myday.image + '"/></div>' + '<h5 class="calendarCharityHead">' + dayarray[i].charity[0].display_shortname + '</h5><span class="displayBlock">' + dayarray[i].myday.name + '</span>' + '<p>' + dayarray[i].title + '</p><p>(' + fullTime + ' hours)</div></div>';
                        }

                        if (!calendarEvents[datestr]) {
                            //NO EVENTS YET FOR THIS DATE
                            calendarEvents[datestr] = '<div class="calBlock" data-id="' + dayarray[i].id + '"' + addOpacity + ' ><div id=charid' + dayarray[i]['charity id'] + '></div><div class=charid' + dayarray[i]['charity id'] + '>' + '<h5 class="calendarCharityHead">' + dayarray[i].charity[0].display_shortname + '</h5>' + '<p>' + dayarray[i].title + '</p></div></div>';
                            calendarEventsNum[datestr] = 1;
                        } else {
                            //DATE ALREADY EXISTS, SO ADD THIS EVENT TO IT.
                            calendarEvents[datestr] = calendarEvents[datestr] + '<div class="calBlock" data-id="' + dayarray[i].id + '"' + addOpacity + ' ><div class=charid' + dayarray[i]['charity id'] + '>' + '<h5 class="calendarCharityHead">' + dayarray[i].charity[0].display_shortname + '</h5>' + '<p>' + dayarray[i].title + '</p></div></div>';
                            calendarEventsNum[datestr]++;
                        }
                        // if (calendarEvents[myDay] == dayarray[i].date) {
                        // 	calendarEvents[myDay] = '<div class="calBlock" style="display: none">my day</div>'
                        // }
                        //                   $('.myDayContent div div').each(function (index, element) {
                        // 	if($(this).hasClass('charid'+dayarray[i]['charity id'])) {
                        // 		alert('i has class');
                        // 		$(this).removeClass('charid'+dayarray[i]['charity id']);
                        // 	}

                    }
                    //('.charityEventDetails').toggle();
                    //ADD a JQUERY click method that uses the charity day's id and that will open the dialog box that .  That box will call add wellday, remove wellday etc...
                    //http://local.droga5.com/welldays/mydays/add/36_0_33
                    //Do something
                }
                self.caldata = calendarEvents;
                self.caldataNum = calendarEventsNum;
                //window.caldataNum=self.caldataNum;
                //window.caldata=self.caldata;
                self.mydaydata = myDays;
    
                self._generateTemplate(callback);
            });
            //WHY IS GETJSON BEING CALLED TWICE???  ARE YOU TRYING TO GET THE LIST OF CHARITIES?
            //IF SO.... http://local.droga5.com/welldays/charities/get
            //BUT... WHY CALL IT HERE?  SHOULDNT HAPPEN EACH TIME.... JUST NEED TO GET CHARITIES ONCE!!!!
            /*$.getJSON(urlstr, function(data) {
				                var filterChr = data.response;
				                var i = 0; 
				                var char = $('.charid' + filterChr[i]['charity id'] );
				               	
				$('.filterContainer').each(function(data, callback){
					console.log(filterChr[i]['charity id'])
					//alert('hai');
             		$(this).append(
             			'(<input name=name type=checkbox class= charid' + filterChr[i]['charity id'] + ' ).attr("checked", "checked")/>' + 
             				'<lable>' + filterChr[i].charity[0].display_shortname + '</label>');
            	});				

            	$('.charid'+ filterChr[i]['charity id']).click(function(){
				    if ($('.charid'+ filterChr[i]['charity id']).attr('checked')) {
				        alert('hai mom');
				    }
				}) 

            });*/
            //alert ("set the var here"+urlstr);
            //this._generateTemplate( callback );
        },
        getCharities: function() {
            console.log("getCharities");
            var urlstr = "/welldays/charities/get";
            var self = this;
            this.charityArray = [];
            $.getJSON(urlstr, function(data) {
                var charities = data.response;
                
                self.charityArray=[];
                for (var i = 0; i < charities.length; i++) {
                    self.charityArray.push(charities[i].id);
                    $('.filterContainer').append('<li><div><input name=' + charities[i].id + ' type=checkbox id=charname' + charities[i].id + ' class=charid' + charities[i].id + ' checked >' + '<label for=charname' + charities[i].id + ' >' + charities[i].display_shortname + '</label></div></li>');
                    $('.charid' + charities[i].id).click(function() {
                    	//alert('hai mom'+this.name+" "+self.charityArray);
                        if ($(this).is(':checked')) {
                            self.charityArray.push(this.name);
                            $("#charname"+self.remainingcheckbox).prop("disabled", false);
                        } else {
                            //
                            //if (self.charityArray.length>1) {
                            	self.charityArray.splice(self.charityArray.indexOf(this.name), 1);
                            	if (self.charityArray.length==1) {
                            		//disable the remaining checkbox .. it has the id that is left in the array.. which is self.charityArray[0];
                            		var remainingcheckbox = self.charityArray[0];
                            		self.remainingcheckbox=remainingcheckbox;
                            		//add javascript to 
                            		$("#charname"+remainingcheckbox).prop("disabled", true);


                            	}
                            //} else {

                            ///}
                            
                        }
                        //-------------I WILL ADD TO THE RESPONSE SOMETHING TO TELL YOU IF THIS PARTICULAR MONTH IS ONE WITH MY DAY IN IT AND IF IT HAS TODAY IN IT
                        //IF SO, YOU WILL UPDATE THE CLASSES OF $('#custom-myday') AND $('custom-current')
                        self.filterData(self.charityArray.toString(), function() {
                            //console.log("filterdata done");
                        });
                    })
                }
            });
        },
        /************************* 
         ******PUBLIC METHODS *****
         **************************/
        getYear: function() {
            return this.year;
        },
        getMonth: function() {
            return this.month + 1;
        },
        getMonthName: function() {
            return this.options.displayMonthAbbr ? this.options.monthabbrs[this.month] : this.options.months[this.month];
        },
        // gets the cell's content div associated to a day of the current displayed month
        // day : 1 - [28||29||30||31]
        getCell: function(day) {
            var row = Math.floor((day + this.startingDay - this.options.startIn) / 7),
                pos = day + this.startingDay - this.options.startIn - (row * 7) - 1;
            return this.$cal.find('div.fc-body').children('div.fc-row').eq(row).children('div').eq(pos).children('div');
        },
        setData: function(caldata) {
            caldata = caldata || {};
            $.extend(this.caldata, caldata);
            this._generateTemplate();
        },
        // goes to today's month/year
        gotoNow: function(callback) {
            console.log("gotonow");
            this.month = this.today.getMonth();
            this.year = this.today.getFullYear();
            this._getJSONData(callback);
            //this._generateTemplate( callback );
        },
                // goes to today's month/year
        gotoCal: function(callback) {

                        (function($){
    $.getQuery = function( query ) {
        query = query.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
        var expr = "[\\?&]"+query+"=([^&#]*)";
        var regex = new RegExp( expr );
        var results = regex.exec( window.location.href );
        if( results !== null ) {
            return results[1];
            return decodeURIComponent(results[1].replace(/\+/g, " "));
        } else {
            return false;
        }
    };
})(jQuery);


    var monthquery = parseInt($.getQuery('month'));
    var yearquery = parseInt($.getQuery('year'));

    if (monthquery) {
                this.month=monthquery-1;
                this.year=yearquery;
            }

            console.log("gotoCal"+this.month);
            console.log("gotoCal"+this.year);
            if (this.month==null) {
                this.gotoNow(callback);
            } else {
                this._getJSONData(callback);
            }
        },
         gotoMyDay: function(callback) {
            var urlstr = "/welldays/mydays/date/next";
            var self = this;
            $.getJSON(urlstr, function(data) {
                var response = data.response;
                self.month = parseInt(response.month)-1;
            	self.year = parseInt(response.year);
            	//NOW NEED TO UPDATE CALENDAR AND CLOSE THIS WINDOW
                self._hideCharityEventDetails();
                self.filterData(null, callback);
            });

           
            //this._generateTemplate( callback );
        },

        // whenever you filter by charity, you can call cal.filterData( charityid, callbackmethod );
        //cal.filterData("1,2,3,5", callbackMethod);
        filterData: function(charityid, callback) {
            console.log("filterData charityid  -- "+charityid);
            if (charityid) {
            	this.charityid = charityid;
            }
            
            this._getJSONData(callback);
            //this._generateTemplate( callback );
        },
        onCharityEventClick: function(callback) {
            console.log("onCharityEventClick")
            this.cellBlock = charityEvent;
            this._generateTemplate(callback);
        },
        // goes to month/year
        goto: function(month, year, callback) {
            this.month = month;
            this.year = year;
            this._generateTemplate(callback);
        },
        gotoPreviousMonth: function(callback) {
            this._hideCharityEventDetails();
            this._move('month', 'previous', callback);
                        if (!callback) {
                $( '#custom-month' ).html( this.getMonthName() );
                $( '#custom-year' ).html( this.getYear() );
                $('html, body').animate({ scrollTop: 0 }, 'slow');
            }
        },
        gotoPreviousYear: function(callback) {
            this._move('year', 'previous', callback);
        },
        gotoNextMonth: function(callback) {
            this._hideCharityEventDetails();
           this._move('month', 'next', callback);
            if (!callback) {
                $( '#custom-month' ).html( this.getMonthName() );
                $( '#custom-year' ).html( this.getYear() );
                $('html, body').animate({ scrollTop: 0 }, 'slow');
            }
        },
        gotoNextYear: function(callback) {
            this._move('year', 'next', callback);
            
        }
    };
    var logError = function(message) {
        if (window.console) {
            window.console.error(message);
        }
    };
    $.fn.calendario = function(options) {
        var instance = $.data(this, 'calendario');
        if (typeof options === 'string') {
            var args = Array.prototype.slice.call(arguments, 1);
            this.each(function() {
                if (!instance) {
                    logError("cannot call methods on calendario prior to initialization; " + "attempted to call method '" + options + "'");
                    return;
                }
                if (!$.isFunction(instance[options]) || options.charAt(0) === "_") {
                    logError("no such method '" + options + "' for calendario instance");
                    return;
                }
                instance[options].apply(instance, args);
            });
        } else {
            this.each(function() {
                if (instance) {
                    instance._init();
                } else {
                    instance = $.data(this, 'calendario', new $.Calendario(options, this));
                }
            });
        }
        return instance;
    };
})(jQuery, window);