// make a object containing coordinates from a string
function makeCoordinatesObject(coordinatesString) {
	if (typeof coordinatesString == "string") { 
		let array = coordinatesString.split("-");
		return {
			x: parseInt(array[0]),
			y: parseInt(array[1]),
			toString: function () {
				return `${this.x}-${this.y}`;
			}
		}
	}
	return coordinatesString;
}

function pieceAnimation(from, to, special = true) {

	let toCor = makeCoordinatesObject(to);
	let fromCor = makeCoordinatesObject(from);

	// castling animation 
	if (($(`#${from} > div`).attr("data-piece") == 'king') &&
		(Math.abs(fromCor.x - toCor.x) == 2) && special) {
		
		// 2 = left, -2 = right
		let towerCorOld = fromCor.x - toCor.x == 2 ? `0-${fromCor.y}` : `7-${fromCor.y}`;
		let towerCorNew = fromCor.x - toCor.x == 2 ? `2-${fromCor.y}` : `4-${fromCor.y}`;
		
		pieceAnimation(towerCorOld, towerCorNew);
	}

	$('.move-from').removeClass('move-from');
	$('.hint-from').removeClass("hint-from");
	$('.move-to').removeClass('move-to');
	$('.hint-to').removeClass("hint-to");

	let toElement   = $(`#${toCor}`);
	let fromElement = $(`#${fromCor}`);

	let cellHeight  = $('td').outerHeight();
	let cellWidth   = $('td').outerWidth();

	let data        = $(`#${fromCor} div`);

	data.css('left', ((toCor.x - fromCor.x) * cellWidth) + "px");
	data.css('top', ((toCor.y - fromCor.y) * cellHeight)+ "px");

	setTimeout(() => {

		if (toElement.html() != '') {
			let div = $(`#${toCor} div`);
			let team = div.attr("class");
			$(`.${team}.captured`).append($(`#${toCor} i`));
		}

		data.css('left', '0px');
		data.css('top', '0px');

		if (special) {
			fromElement.addClass("move-from");
			toElement.addClass("move-to");
		}

		if (special && $(`#${fromCor} div`).attr('data-piece') == 'pawn') {
			if (toCor.y == 0 || toCor.y == 7) {
				toElement.html(`<div data-piece="queen" style="${data.attr('style')}" class="${data.attr('class')}"><i class="fa-solid fa-chess-queen"></i></div>`);
				fromElement.html('');
				return;
			}
		}

		toElement.html('');
		toElement.append(data);
	}, 500);
}

function move(from, to) {
    makeBoardStatic();

	pieceAnimation(from, to);

	const url       = 'assets/php/calculate_move.php';
	const postData  = {
		from: from,
		to:   to,
	}

	$.post(url, postData, function(data, state) {
		botMove = JSON.parse(data);

        // wait for animation to finish. then move piece
		setTimeout(() => {

            makeBoardStatic();

			if (botMove['move'] != undefined) {
				let from = createCor(botMove.move.from).toString();
				let to   = createCor(botMove.move.to).toString();
                pieceAnimation(from, to);
			}

			if (botMove.status == 'checkmate') {
				showEndScreen(`${botMove.winner} won`);
                return;
			} else if (botMove.status == 'stalemate') {
				showEndScreen(`Draw`);
                return;
			}
            
            setTimeout(() => {
                makeBoardInteractive();
            }, 300)

		}, 600);
	});
}

function createCor(cor) {
	return {
		x: cor.x,
		y: cor.y,
		toString: function() {
			return `${this.x}-${this.y}`;
		}
	}
}

function showEndScreen(text) {
	setTimeout(() => {
		$('#overlay').addClass('show');
		$('#overlay > h2').html(text);
	}, 800)
}

function reloadCapturedPieces() {
	const teams = ['top', 'bottom'];

	const piecesInTeam = {
		pawn:  	8,
		horse:  2,
		bishop: 2,
		tower:  2,
		queen:  1,
		king:  	1,
	};

	const icons = {
		tower: 	'<i class="fa-solid fa-chess-rook-piece"></i>',
		horse: 	'<i class="fa-solid fa-chess-knight"></i>',
		bishop: '<i class="fa-solid fa-chess-bishop"></i>',
		queen: 	'<i class="fa-solid fa-chess-queen"></i>',
		king: 	'<i class="fa-solid fa-chess-king"></i>',
		pawn: 	'<i class="fa-solid fa-chess-pawn"></i>',
	};

	teams.forEach(team => {
		let html = '';
		for (let pieceName in piecesInTeam) {
			let total 	= piecesInTeam[pieceName];
			let current = total - $(`.${team}[data-piece=${pieceName}]`).toArray().length;

			for (let i = 0; i < current; i++) {
				html += icons[pieceName];
			}
		}

		$(`.captured.${team}`).html(html);
	});
}

$('#hint').click(function() {
    const url = 'assets/php/hint.php';
    const postData = {};

    $.post(url, postData, function(data, state) {
        hint = JSON.parse(data);
        
        $('#'+createCor(hint.from).toString()).addClass("hint-from");
        $('#'+createCor(hint.to).toString()).addClass("hint-to");
    });
    $('.hint-from').removeClass("hint-from");
    $('.hint-to').removeClass("hint-to");
})

$('#undo').click(function() {
    makeBoardStatic();

    const url = 'assets/php/undo.php';
    const postData = {};
    $.post(url, postData, function(data, state) {

        if (data == '') { return };

        animationData   = JSON.parse(data);
        let delay       = 100;

        for (let animation in animationData) {
            animation = animationData[animation];

            setTimeout(() => {
                $(`#${animation.from}`).html(animation.square_state.current);
                pieceAnimation(animation.from, animation.to, false);

                setTimeout(() => {
                    $(`#${animation.from}`).append(animation.square_state.before);
                }, 550);
            }, delay);

            delay += 600;
        }

        setTimeout(() => {
            makeBoardInteractive();
            reloadCapturedPieces();
        }, delay);
    });
})

function makeBoardStatic() {
    $('button').attr('disabled', true);

    $('.hint-from').removeClass("hint-from");
	$('.move-from').removeClass('move-from');

	$('.move-to').removeClass('move-to');
	$('.hint-to').removeClass("hint-to");

	$('.focus').removeClass('focus');

    $('td').each(function(){
		$(this).off();
		$(this).removeClass('path');
	});
}

function makeBoardInteractive() {
    $.post('assets/php/total_movements.php', {}, (data, status) => {
        let piecesMovements = JSON.parse(data);

        for (let pieceCor in piecesMovements) {

            let movements = piecesMovements[pieceCor];
            let square = $(`#${pieceCor}`);

            square.click(function() {

                //
                // remove existing focus & path
                //
                $('.focus').removeClass('focus');
                $('.path').each(function() {
		            $(this).removeClass('path');
                    $(this).off();
                })

                //
                // set new events
                //

                square.addClass('focus');
                movements.forEach(squareCoordinates => {
                    let pathSquare = $(`#${squareCoordinates}`);

                    pathSquare.addClass('path');
                    pathSquare.click(function() { move(pieceCor, squareCoordinates); })
                });
                
            })
        }// for

        $('button').attr('disabled', false);
    })// post
}


makeBoardInteractive();