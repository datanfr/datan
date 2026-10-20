<!--

This is the code used for the banner for the quiz.
It was displayed on the page bottom on mobile devices ; and no the side on desktop.
After the user closed it, the banner was no longer displayed for the time of the session.

The JS code is at the end of this document.

-->

<div class="shadow py-md-3" id="dialogueBoxQuiz" displayed="false" already_closed="<?= $this->session->userdata("already_closed") !== null ? $this->session->userdata("already_closed") : "0" ?>">
  <span class="close cursor-pointer" id="closeDialogueBoxQuiz">
    <span aria-hidden="true">&times;</span>
  </span>
  <div class="text-container d-flex flex-column justify-content-around">
    <object class="d-none d-md-block" style="height: 110px" data="<?= asset_url()."imgs/quiz/phone.svg" ?>" type="image/svg+xml"></object>
    <span class="text text-center font-weight-bold mt-2 mt-md-0">Êtes-vous proche de votre député ?</span>
    <div class="d-flex justify-content-center">
      <a class="btn btn-light url_obf text-center text-dark my-3 my-md-0" url_obf="<?= url_obfuscation("https://quiz.datan.fr") ?>">Faites le quiz !</a>
    </div>
  </div>
</div>

<script type="text/javascript">
  /*
  ##########
  DIALOGUE BOX QUIZ
  ##########
  */
  $(document).scroll(function () {
    var y = $(this).scrollTop();
    if (y > 400 && $("#dialogueBoxQuiz").attr("displayed") === "false" && $("#dialogueBoxQuiz").attr("already_closed") === "0") {
      $('#dialogueBoxQuiz').fadeIn();
      $("#dialogueBoxQuiz").attr("displayed", "true");
    }
  });
  $("#closeDialogueBoxQuiz").click(function(){
    $('#dialogueBoxQuiz').fadeOut();
    $.get(get_base_url() + "/export/set_session/" + 1, function (result) {
      console.log(result);
    })
  })
</script>
