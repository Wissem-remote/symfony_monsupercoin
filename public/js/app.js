const onSubmit = document.querySelector('.email');
const form = document.getElementById('formSubmitButton');
const message = document.querySelector('.check');


onSubmit.addEventListener('click',e => {
    e.preventDefault();
    //console.log('hello');
    const value = Object.fromEntries(new FormData(e.path[1]));
    emailjs.send("service_gzwqu0t", "template_it42osy", value, "user_Q6xTN3DJd6AoRLdHBeMdt")
        .then((res) => {
            console.log('sucess', res)
        }, (err) => {
            console.log('error', err)
        });
    message.classList.toggle('check');
    e.path[1].reset();

});
