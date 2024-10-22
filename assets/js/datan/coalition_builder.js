$(document).ready(function(){
    var circles = $('#hemicycle_seats circle');
    var seats = {};

    function addGroup(groups, groupName, seats, color){
        groups[groupName] = {
            seats: seats,
            color: color
        }
    }

    function removeGroup(groups, groupName){
        delete groups[groupName]
    }

    function updateIndex(groups){
        var startIndex = 0;
        Object.entries(groups).forEach(([key, group]) => {
            group.startIndex = startIndex;
            startIndex += group.seats;
        })
    }

    function updateHemicycle(groups){
        let lastIndex = 0;
        Object.entries(groups).forEach(([key, group]) => {
            circles.slice(group.startIndex, group.startIndex + group.seats).css('fill', group.color);
            lastIndex = group.startIndex + group.seats;
        })
        circles.slice(lastIndex, 577).css('fill', '#DDDDDD');
    }

    function updateText(groups){
        var textSeats = $('#textSeats');
        var textPct = $('#textPct');
        var textResult = $('#textResult');
        let total = 0;
        var pct = 0;
        $.each(groups, function(key, group) {
            total += group.seats;
        });
        pct = Math.round(total / 577 * 100);
        if(total >= 1) {
            textSeats.text(total + ' sièges');
        } else {
            textSeats.text(total + ' siège');
        }
        if(total >= 289) {
            textResult.text("Félicitations. Vous avez une majorité");
            textResult.attr('class', 'text-success');
        } else {
            textResult.text("Vous n'avez pas la majorité");
            textResult.attr('class', 'text-danger');
        }
        textPct.text(pct);
    }

    $('.switch_groups').change(function(){
        var groupToChange = this.id;
        var seatsToChange = groups[groupToChange].seats;
        var colorToChange = groups[groupToChange].color;

        // Change state
        if(this.checked) {
            addGroup(seats, groupToChange, seatsToChange, colorToChange);
        } else {
            removeGroup(seats, groupToChange);
        }
        updateIndex(seats);
        updateHemicycle(seats);
        updateText(seats);  
    })

    $('#custom-reset').on('click', function(){
        seats = {};
        updateHemicycle(seats);
        updateText(seats); 
        $('.switch_groups').prop('checked', false);
    });
     
})