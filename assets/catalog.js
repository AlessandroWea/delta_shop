import './styles/catalog.css';

let catalog = document.querySelector('.catalog');

let active = catalog.querySelector('.active');

let active_link = catalog.querySelector('.active > a');

active_link.style.fontWeight = 'bold';

//find topmost parent
let current = active.parentElement;

let path = [];

path.push(active);

while(current.parentElement !== catalog)
{
    path.push(current);
    current = current.parentElement;
}

console.log(path);
if(active.children.length > 1)
    path.push(active.children[1]);

path.forEach(element => {
    console.log(element);
    element.classList.add('active');
}); 