// make a object containing coordinates from a string
function makeCoordinatesObject(coordinatesSring) {
	if (typeof coordinatesSring == "string") { 
		let array = coordinatesSring.split("-");
		return {
			x: parseInt(array[0]),
			y: parseInt(array[1]),
			toString: function () {
				return `${this.x}-${this.y}`;
			}
		}
	}
	return coordinatesSring;
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

async function makeBoardInteractive() {
	$(document).ready(function() {

		const url = 'assets/php/total_movements.php';
		$.post(url, {}, (data, status) => {
			let piecesObject = JSON.parse(data);

			for (let pieceCor in piecesObject) {
				let movements = piecesObject[pieceCor];
				let square = $(`#${pieceCor}`);
				square.on('click', () => {
					resetSquares();

					square.addClass('focus');
					movements.forEach((square) => {
						let pathSquare = $(`#${square}`);
						pathSquare.addClass('path');
						pathSquare.on('click', () => {
							move(pieceCor, square);
						})
					});
				})
			}
		})
	})
}

async function move(from, to) {
	const url = 'assets/php/calculate_move.php';
	const postData = {
		from: from,
		to:   to,
	}

	$.post(url, postData, function(data, state) {
		botMove = JSON.parse(data);

		setTimeout(() => {

			if (botMove['move'] != undefined) {
				let from = createCor(botMove.move.from);
				let to   = createCor(botMove.move.to);
				resetSquares();
				setTimeout(() => {
					pieceAnimation(from.toString(), to.toString());
				}, 450);
			}

			if (botMove.status == 'checkmate') {
				showEndScreen(`${botMove.winner} won`);
			} else if (botMove.status == 'stalemate') {
				showEndScreen(`Draw`);
			} else {
				makeBoardInteractive();
			}

		}, 300);
	});

	resetSquares();
	$('td').each(function() {
		$(this).off();
	});
	pieceAnimation(from, to);
}

function resetSquares() {
	$('.hint-from').removeClass("hint-from");
	$('.move-from').removeClass('move-from');
	$('.move-to').removeClass('move-to');
	$('.hint-to').removeClass("hint-to");
	$('.focus').removeClass('focus');
	$('.path').each(function(){
		$(this).removeClass('path');
		$(this).off();
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

document.getElementById("hint").onclick = function() {
	$(document).ready(function() {
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
}

document.getElementById("undo").onclick = function() {
    $('#undo').attr('disabled', true);
	$(document).ready(function() {
		const url = 'assets/php/undo.php';
		const postData = {};

		$.post(url, postData, function(data, state) {

			if (data == '')  return;

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
				resetSquares();
				makeBoardInteractive();
				reloadCapturedPieces();

                $('#undo').attr('disabled', false);
			}, delay);
		});
		resetSquares();
	})
}

makeBoardInteractive();