const onSubmit = document.querySelector('.email');
const form = document.getElementById('formSubmitButton');

onSubmit.addEventListener('click', (e) => {
    const value = Object.fromEntries(new FormData(e.path[1]));
    emailjs.send("service_gzwqu0t", "template_it42osy", value, "user_Q6xTN3DJd6AoRLdHBeMdt")
        .then((res) => {
            console.log('sucess', res)
        }, (err) => {
            console.log('error', err)
        });
    
})
