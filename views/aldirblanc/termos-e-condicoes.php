<p>OS TERMOS AQUI</p>

<label for="myCheck">aceito:</label> 
<input type="checkbox" id="myCheck" >

<button onclick="goToNextPage()">Click me</button>

<script>
    
function goToNextPage() {
    var checkBox = document.getElementById("myCheck");
    if (checkBox.checked == true){
        //leva pra outra tela
        document.location = `${MapasCulturais.baseURL}aldirblanc/selecionaragente`;
    } else {
        alert("PRECISA ACETAR OS TERMOS")
    }
}

</script>