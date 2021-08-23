<mj-section background-color="#ffffff" background-repeat="repeat" background-size="auto" padding="20px 15px" text-align="center" vertical-align="top">
  <mj-column vertical-align="top">
    <mj-text>
      <span class="title"><b>Les derniers votes de l'Assemblée nationale</b></span>
    </mj-text>
    <mj-text padding-top="0px">
      <span class="subtitle"><?= ucfirst($month) ?> <?= $year ?></span>
    </mj-text>
    <mj-text padding-top="40px">
      Il y a eu <?= $votesN ?> votes à l'Assemblée nationale, en <?= $month.' '.$year ?>.
    </mj-text>
  </mj-column>
</mj-section>


<mj-section background-color="white">

  <!-- Left image -->
  <mj-column>
    <mj-image width="200px"
              src="https://designspell.files.wordpress.com/2012/01/sciolino-paris-bw.jpg" />
  </mj-column>

  <!-- right paragraph -->
  <mj-column>
    <mj-text font-style="italic"
             font-size="20px"
             font-family="Helvetica Neue"
             color="#626262">
        Find amazing places
      </mj-text>

      <mj-text color="#525252">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin rutrum enim eget magna efficitur, eu semper augue semper. Aliquam erat volutpat. Cras id dui lectus. Vestibulum sed finibus lectus.</mj-text>

  </mj-column>
</mj-section>
