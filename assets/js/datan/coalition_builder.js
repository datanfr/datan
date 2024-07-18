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
        var textSeats = document.getElementById('textSeats');
        var textPct = document.getElementById('textPct');
        var textResult = document.getElementById('textResult');
        let total = 0;
        var pct = 0;
        Object.entries(groups).forEach(([key, group]) => {
            total = total + group.seats;
        })
        pct = Math.round(total / 577 * 100);
        if(total > 0) {
            textSeats.textContent = total + ' sièges';
        } else {
            textSeats.textContent = total + ' siège';
        }
        if(total > 289) {
            textResult.textContent = "Félicitations. Vous avez une majorité";
            textResult.className = 'text-success';
        } else {
            textResult.textContent = "Vous n'avez pas la majorité";
            textResult.className = 'text-danger';
        }
        textPct.textContent = pct;
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
        seats = new Array();
        updateHemicycle(seats);
        updateText(seats); 
        $('.switch_groups').prop('checked', false);
    });
     
})