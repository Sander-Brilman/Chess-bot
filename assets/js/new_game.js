function updateLevel() {
	let displayText = '0';
	let number      = Math.round($('#level').val() / 200) * 200;
	switch (number) {
		case 200:
			displayText = '1';
			break;

		case 400:
			displayText = '2';
			break;

		case 600:
			displayText = '3';
			break;

		case 800:
			displayText = '4';
			break;

		case 1000:
			displayText = '5';
			break;
	}
	$('label > span').html(` - ${displayText}`);
}

setInterval(() => {
	updateLevel();
	updateColors();
}, 100);

document.querySelectorAll('.radio-container').forEach(element => {
	element.onclick = function() {
		if (element.querySelector('input').getAttribute('checked') == null) {
			$(".radio-container > input").each(function() {
                $(this).removeAttr("checked");
            });

			toggleColorPicker(false);
			element.querySelector('input').setAttribute('checked', true);
		}
	}
});


document.querySelectorAll('.color-row').forEach(row => {
	row.onclick = function() {
		row.querySelector('input').click();
	}
})

let active = false;
$('#toggle').click(function() {
	toggleColorPicker();
})

function toggleColorPicker(state = null) {
    active = state == null ? !active : state;

    let degrees     = active ? '0' : '90';
    let height      = active ? '210' : '90';
    let time_out    = active ? 500 : 75
    let visibility  = active ? 'visible' : 'hidden';

    $('#toggle').css('transform', `rotate(${degrees}deg)`);
    $('.input-row:first-of-type').css('height', `${height}px`);

    setTimeout(() => {
        $('.color-picker').css('visibility', visibility);
    }, time_out);
}

function updateColors() {
	let color = $('#custom_shadow').val();
	$('.radio-container:last-of-type > i').css('textShadow', `${color} 0px 0px 4px`);

	color = $('#custom_color').val();
	$('.radio-container:last-of-type > i').css('color', color);
}


