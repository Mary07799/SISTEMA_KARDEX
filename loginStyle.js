// Anima las figuras constantemente con valores aleatorios
function animateBackground() {
  anime({
    targets: '.square, .circle, .triangle',
    translateX: () => anime.random(-500, 500),
    translateY: () => anime.random(-300, 300),
    rotate:     () => anime.random(0, 360),
    scale:      () => anime.random(0.2, 2),
    duration: 2500,
    easing: 'easeInOutQuad',
    complete: animateBackground
  });
}

animateBackground();
