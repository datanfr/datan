 <!-- BLOC PARRAINAGES -->
 <?php if ($parrainage): ?>
   <div class="mt-5 bloc-elections-history">
     <h2 class="mb-4 title-center">Ses parrainages présidentiels</h2>
     <p>
       <?= $title ?> a déjà parrainé un candidat à l'élection présidentiel pendant son mandat de député<?= $gender['e'] ?>.
     <table class="table">
      <caption class="sr-only">Les parrainages de <?= $title ?></caption>
      <thead class="sr-only">
        <tr>
          <th scope="col">Élection</th>
          <th scope="col">Candidat</th>
        </tr>
      </thead>
       <tbody>
         <tr>
           <td class="font-weight-bold">Élection présidentielle 2024</td>
           <td>Parrainagé accordé à <b><?= $parrainage['candidat'] ?></b></td>
         </tr>
       </tbody>
     </table>
     </p>
   </div>
 <?php endif; ?>