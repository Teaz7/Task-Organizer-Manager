function createPet(options = {}) {
    const selectedImage = options.image || 'default.png';

    const existingPet = document.getElementById('pet');
    if (existingPet) {
        existingPet.style.backgroundImage = `url('${selectedImage}')`;
        return;
    }

    const style = document.createElement('style');
    style.textContent = `
        #pet {
            position: absolute;
            left: 50px;
            top: 50px;
            width: 100px;
            height: 100px;
            background: url('${selectedImage}') no-repeat center/contain;
            z-index: 9999;
            cursor: grab;
            transition: left 0.3s, top 0.3s;
            user-select: none;
        }
        #pet.dragging {
            cursor: grabbing;
            transition: none;
        }
        #speech {
            position: absolute;
            left: 160px;
            top: 120px;
            background: #fffbe7;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 8px 16px;
            font-family: sans-serif;
            font-size: 16px;
            z-index: 10000;
            display: none;
            pointer-events: none;
        }
    `;
    document.head.appendChild(style);

    const pet = document.createElement('div');
    pet.id = 'pet';
    const speech = document.createElement('div');
    speech.id = 'speech';
    document.body.appendChild(pet);
    document.body.appendChild(speech);

    let tickleCount = 0;
    let tickleTimeout = null;

    function showSpeech(text) {
        speech.textContent = text;
        speech.style.display = 'block';
        updateSpeechPosition();
        setTimeout(() => {
            speech.style.display = 'none';
        }, 1500);
    }

    pet.addEventListener('click', () => {
        tickleCount++;
        if (tickleTimeout) clearTimeout(tickleTimeout);
        tickleTimeout = setTimeout(() => {
            if (tickleCount === 1) {
                showSpeech('Hello! Pet me more!');
            } else if (tickleCount > 1) {
                showSpeech('Hehe! That tickles!');
            }
            tickleCount = 0;
        }, 400);
    });

    pet.addEventListener('dblclick', (e) => {
        e.preventDefault();
        tickleCount = 0;
        showSpeech('Yum! Thank you for the food!');
    });

    function movePetToEdge() {
        const edge = Math.floor(Math.random() * 4);
        let x = 0, y = 0;
        const margin = 10;
        const petW = 100, petH = 100;
        switch (edge) {
            case 0:
                x = Math.random() * (window.innerWidth - petW - margin * 2) + margin;
                y = margin;
                break;
            case 1:
                x = window.innerWidth - petW - margin;
                y = Math.random() * (window.innerHeight - petH - margin * 2) + margin;
                break;
            case 2:
                x = Math.random() * (window.innerWidth - petW - margin * 2) + margin;
                y = window.innerHeight - petH - margin;
                break;
            case 3:
                x = margin;
                y = Math.random() * (window.innerHeight - petH - margin * 2) + margin;
                break;
        }
        pet.style.left = `${x}px`;
        pet.style.top = `${y}px`;
        updateSpeechPosition();
    }

    setInterval(movePetToEdge, 5000);

    let isDragging = false;
    let offsetX = 0, offsetY = 0;

    pet.addEventListener('mousedown', function(e) {
        isDragging = true;
        pet.classList.add('dragging');
        const rect = pet.getBoundingClientRect();
        offsetX = e.clientX - rect.left;
        offsetY = e.clientY - rect.top;
        document.body.style.userSelect = 'none';
    });

    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        let x = e.clientX - offsetX;
        let y = e.clientY - offsetY;
        x = Math.max(0, Math.min(window.innerWidth - pet.offsetWidth, x));
        y = Math.max(0, Math.min(window.innerHeight - pet.offsetHeight, y));
        pet.style.left = `${x}px`;
        pet.style.top = `${y}px`;
        updateSpeechPosition();
    });

    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            pet.classList.remove('dragging');
            document.body.style.userSelect = '';
        }
    });

    function updateSpeechPosition() {
        const rect = pet.getBoundingClientRect();
        let left = rect.left + 110;
        let top = rect.top + 20;
        if (left + speech.offsetWidth > window.innerWidth) {
            left = rect.left - speech.offsetWidth - 10;
        }
        if (top + speech.offsetHeight > window.innerHeight) {
            top = rect.top - speech.offsetHeight - 10;
        }
        speech.style.left = `${left}px`;
        speech.style.top = `${top}px`;
    }

    // Initial position
    pet.style.left = '50px';
    pet.style.top = '50px';
    updateSpeechPosition();
}
