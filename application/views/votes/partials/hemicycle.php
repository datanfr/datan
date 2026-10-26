<?php
  $hemicycle_colors = array(
    'pour' => '#00B794',
    'abstention' => '#FFBA49',
    'contre' => '#C5283D',
    'non-votant' => '#8C8C8C',
    'absent' => '#DDDDDD'
  );
  $hemicycle_labels = array(
    'pour' => 'Pour',
    'abstention' => 'Abstention',
    'contre' => 'Contre',
    'non-votant' => 'Non-votant',
    'absent' => 'Absent'
  );
  // Singular / plural forms for the group counts
  $hemicycle_forms = array(
    'pour' => array('pour', 'pour'),
    'abstention' => array('abstention', 'abstentions'),
    'contre' => array('contre', 'contre'),
    'non-votant' => array('non-votant', 'non-votants'),
    'absent' => array('absent', 'absents')
  );
  // Motion de censure: only MPs in favour take part in the vote
  if ($vote_type == 'motion de censure') {
    $hemicycle_labels['absent'] = "N'a pas voté";
    $hemicycle_forms['absent'] = array("n'a pas voté", "n'ont pas voté");
  }
  $hemicycle_counts = array_count_values(array_column($hemicycle['seats'], 'vote'));
  $hemicycle_voted = 0;
  foreach (array('pour', 'abstention', 'contre') as $vote) {
    $hemicycle_voted += isset($hemicycle_counts[$vote]) ? $hemicycle_counts[$vote] : 0;
  }

  // Compact data for the hover card (links and portraits are rebuilt in JS)
  $hemicycle_deputes = array();
  foreach ($hemicycle['seats'] as $seat) {
    $depute = $seat['depute'];
    $hemicycle_deputes[] = array(
      'n' => $depute['nameFirst'] . ' ' . $depute['nameLast'],
      'd' => trim($depute['departementNom'] . ($depute['departementCode'] ? ' (' . $depute['departementCode'] . ')' : '')),
      'c' => (int) $depute['circo'],
      'i' => substr($depute['mpId'], 2),
      'u' => $depute['dptSlug'] . '/depute_' . $depute['nameUrl']
    );
  }
  $hemicycle_groupes = array();
  foreach ($hemicycle['groupes'] as $groupe) {
    $hemicycle_groupes[$groupe['libelleAbrev']] = array(
      'l' => name_group($groupe['libelle']),
      'c' => $groupe['color'],
      'n' => $groupe['seats'],
      'v' => $groupe['counts']
    );
  }
  $hemicycle_json = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE;
  // Same convention as votes/partials/explain_text.php
  $hemicycle_photos = asset_url() . 'imgs/' . ($legislature >= 17 ? 'deputes_webp/' : 'deputes_nobg_webp/');
?>
<style>
  #vote-hemicycle .hemicycle-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; }
  #vote-hemicycle .hemicycle-modes .btn { padding: .2rem .7rem; font-size: .85rem; }
  #vote-hemicycle .hemicycle-modes .btn + .btn { margin-left: -1px; border-top-left-radius: 0; border-bottom-left-radius: 0; }
  #vote-hemicycle .hemicycle-modes .btn:first-child { border-top-right-radius: 0; border-bottom-right-radius: 0; }
  #vote-hemicycle .hemicycle-wrapper, #vote-hemicycle .hemicycle-plot { position: relative; }
  #vote-hemicycle svg { display: block; width: 100%; height: auto; }
  #vote-hemicycle svg circle[data-g] { cursor: pointer; transition: fill .5s ease, opacity .25s ease, fill-opacity .5s ease; }
  #vote-hemicycle svg.focus circle { opacity: .15; }
  #vote-hemicycle svg.focus circle.active { opacity: 1; }
  #vote-hemicycle svg circle.selected { stroke: #4D5755; stroke-width: 1.2px; }
  #vote-hemicycle .hemicycle-center { position: absolute; left: 30%; right: 30%; bottom: 0; text-align: center; line-height: 1.25; pointer-events: none; }
  /* Real seat plan: thinner selection ring and narrower space between the two lower blocks */
  #vote-hemicycle.hemicycle-reel svg circle.selected { stroke-width: .6px; }
  #vote-hemicycle.hemicycle-reel .hemicycle-center { left: 37%; right: 37%; }
  #vote-hemicycle .hemicycle-center .hemicycle-title { font-weight: 800; font-size: 1.05rem; }
  #vote-hemicycle .hemicycle-center .hemicycle-details { font-size: .8rem; color: #6C757D; }
  #vote-hemicycle .hemicycle-tooltip { position: absolute; z-index: 10; width: 230px; background: #fff; border-radius: 8px; box-shadow: 0 4px 16px rgba(0, 0, 0, .18); padding: 10px; pointer-events: none; font-size: .85rem; }
  #vote-hemicycle .hemicycle-tooltip[hidden] { display: none; }
  #vote-hemicycle .hemicycle-tooltip .hemicycle-card { display: flex; }
  #vote-hemicycle .hemicycle-tooltip img { width: 60px; height: 60px; flex-shrink: 0; margin-right: 10px; object-fit: cover; object-position: top; border-radius: 6px; background: #F2F2F2; }
  #vote-hemicycle .hemicycle-tooltip .hemicycle-name { font-weight: 700; }
  #vote-hemicycle .hemicycle-tooltip .hemicycle-place { color: #6C757D; }
  #vote-hemicycle .hemicycle-tooltip .hemicycle-badge { display: inline-block; color: #fff; font-weight: 700; font-size: .75rem; padding: 1px 8px; border-radius: 10px; margin-top: 4px; }
  #vote-hemicycle .hemicycle-dot { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 4px; vertical-align: -1px; }
  #vote-hemicycle .hemicycle-legend { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; margin-top: 1rem; }
  #vote-hemicycle .hemicycle-legend > span { margin: 0 .5rem; }
  #vote-hemicycle .hemicycle-legend[hidden] { display: none; }
  #vote-hemicycle .hemicycle-groups { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; margin-top: .5rem; font-size: .85rem; }
  #vote-hemicycle .hemicycle-chip { background: none; border: 0; color: inherit; font: inherit; padding: 2px 6px; border-radius: 12px; transition: background-color .2s ease; }
  #vote-hemicycle .hemicycle-chip.active, #vote-hemicycle .hemicycle-chip:focus { background-color: #E9ECEF; outline: none; }
  #vote-hemicycle .hemicycle-chip:focus-visible { box-shadow: 0 0 0 2px #00B794; }
  #vote-hemicycle .hemicycle-source { margin-top: .5rem; text-align: center; font-size: .75rem; color: #6C757D; }
  @media (max-width: 575.98px) {
    /* On mobile, the card and the group details are shown below the hemicycle so they don't hide it */
    #vote-hemicycle .hemicycle-center { left: 25%; right: 25%; }
    #vote-hemicycle.hemicycle-reel .hemicycle-center { position: static; margin-top: .5rem; }
    #vote-hemicycle .hemicycle-center .hemicycle-title { font-size: .85rem; }
    #vote-hemicycle .hemicycle-center .hemicycle-details { font-size: .65rem; }
    #vote-hemicycle.hemicycle-reel .hemicycle-center .hemicycle-details { font-size: .75rem; }
    #vote-hemicycle .hemicycle-center .hemicycle-details.hemicycle-long { display: none; }
    #vote-hemicycle .hemicycle-tooltip { position: static; width: auto; margin-top: .75rem; }
  }
  @media (prefers-reduced-motion: reduce) {
    #vote-hemicycle svg circle[data-g], #vote-hemicycle .hemicycle-chip { transition: none !important; }
  }
</style>
<div class="mt-5<?= $hemicycle['reel'] ? ' hemicycle-reel' : '' ?>" id="vote-hemicycle">
  <div class="hemicycle-header">
    <h2 class="mb-2 mr-3">Le vote dans l'hémicycle</h2>
    <div class="hemicycle-modes mb-2" role="group" aria-label="Couleurs de l'hémicycle">
      <button type="button" class="btn btn-outline-primary active" data-mode="vote" aria-pressed="true">Votes</button>
      <button type="button" class="btn btn-outline-primary" data-mode="groupe" aria-pressed="false">Groupes</button>
    </div>
  </div>
  <div class="hemicycle-wrapper mt-3">
    <div class="hemicycle-plot">
      <!-- Visual only: the accessible details are in the « Les votes des députés et des groupes » table -->
      <svg viewBox="<?= $hemicycle['viewbox'] ?>" aria-hidden="true" focusable="false">
        <?php
          // One line per seat, without indentation, to keep the page light
          foreach ($hemicycle['circles'] as $circle) {
            $seat = $circle['seat'] !== NULL ? $hemicycle['seats'][$circle['seat']] : NULL;
            echo '<circle cx="' . $circle['cx'] . '" cy="' . $circle['cy'] . '" r="' . $circle['r'] . '" fill="' . ($seat ? $hemicycle_colors[$seat['vote']] : '#F2F2F2') . '"';
            echo $seat ? ' data-g="' . html_escape($seat['groupe']) . '" data-v="' . $seat['vote'] . '"/>' . "\n" : '/>' . "\n";
          }
        ?>
      </svg>
      <div class="hemicycle-center">
        <div class="hemicycle-title"><?= count($hemicycle['seats']) ?> députés</div>
        <div class="hemicycle-details hemicycle-long">Survolez un siège ou un groupe</div>
      </div>
    </div>
    <div class="hemicycle-tooltip" hidden></div>
  </div>
  <div class="hemicycle-legend" data-legend="vote">
    <?php foreach ($hemicycle_labels as $vote => $label) : ?>
      <?php if (!empty($hemicycle_counts[$vote])) : ?>
        <span><span class="hemicycle-dot" style="background-color: <?= $hemicycle_colors[$vote] ?>"></span><?= $label ?> (<?= $hemicycle_counts[$vote] ?>)</span>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <div class="hemicycle-legend" data-legend="groupe" hidden>
    <span><span class="hemicycle-dot" style="background-color: #4D5755"></span>A voté (<?= $hemicycle_voted ?>)</span>
    <span><span class="hemicycle-dot" style="background-color: #4D5755; opacity: .2"></span>N'a pas voté (<?= count($hemicycle['seats']) - $hemicycle_voted ?>)</span>
  </div>
  <div class="hemicycle-groups">
    <span class="mr-1"><?= $hemicycle['reel'] ? 'Groupes :' : 'De gauche à droite :' ?></span>
    <?php foreach ($hemicycle['groupes'] as $groupe) : ?>
      <?php if ($groupe['libelleAbrev'] !== '') : ?>
        <button type="button" class="hemicycle-chip" data-g="<?= html_escape($groupe['libelleAbrev']) ?>" title="<?= html_escape(name_group($groupe['libelle'])) ?>">
          <span class="hemicycle-dot" style="background-color: <?= $groupe['color'] ?>"></span><?= html_escape($groupe['libelleAbrev']) ?>
        </button>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php if ($hemicycle['reel']) : ?>
    <p class="hemicycle-source mb-0">Places actuelles des députés, rapprochées de leur groupe au moment du vote pour ceux qui en ont changé ou ont quitté l'Assemblée depuis. Source : <a href="https://www.assemblee-nationale.fr/dyn/vos-deputes/hemicycle" target="_blank" rel="noopener">Assemblée nationale</a></p>
  <?php endif; ?>
</div>
<script type="text/javascript">
  (function() {
    var deputes = <?= json_encode($hemicycle_deputes, $hemicycle_json) ?>;
    var groupes = <?= json_encode((object) $hemicycle_groupes, $hemicycle_json) ?>;
    var colors = <?= json_encode($hemicycle_colors, $hemicycle_json) ?>;
    var labels = <?= json_encode($hemicycle_labels, $hemicycle_json) ?>;
    var forms = <?= json_encode($hemicycle_forms, $hemicycle_json) ?>;
    var urls = {
      depute: <?= json_encode(base_url() . 'deputes/', $hemicycle_json) ?>,
      photo: <?= json_encode($hemicycle_photos, $hemicycle_json) ?>,
      placeholder: <?= json_encode(asset_url() . 'imgs/placeholder/placeholder-face.png', $hemicycle_json) ?>
    };
    var root = document.getElementById('vote-hemicycle');
    var svg = root.querySelector('svg');
    var wrapper = root.querySelector('.hemicycle-wrapper');
    var tooltip = root.querySelector('.hemicycle-tooltip');
    var center = root.querySelector('.hemicycle-center');
    var centerDefault = center.innerHTML;
    var seats = Array.prototype.slice.call(svg.querySelectorAll('circle[data-g]'));
    // Occupied seats follow the order of the deputes array
    seats.forEach(function(seat, i) { seat.hemicycleIndex = i; });
    var chips = Array.prototype.slice.call(root.querySelectorAll('.hemicycle-chip'));
    var pointerType = 'mouse';
    var focused = null;
    var selected = null;

    function escapeHtml(text) {
      var div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function dot(color, opacity) {
      return '<span class="hemicycle-dot" style="background-color:' + color + (opacity ? ';opacity:' + opacity : '') + '"></span>';
    }

    function focusGroup(abbrev) {
      if (focused === abbrev) {
        return;
      }
      focused = abbrev;
      var groupe = abbrev !== null ? groupes[abbrev] : null;
      svg.classList.toggle('focus', !!groupe);
      seats.forEach(function(seat) { seat.classList.toggle('active', !!groupe && seat.getAttribute('data-g') === abbrev); });
      chips.forEach(function(chip) { chip.classList.toggle('active', !!groupe && chip.getAttribute('data-g') === abbrev); });
      if (!groupe) {
        center.innerHTML = centerDefault;
        return;
      }
      var details = [];
      ['pour', 'abstention', 'contre', 'non-votant', 'absent'].forEach(function(vote) {
        var n = groupe.v[vote];
        if (n > 0) {
          details.push('<span style="color:' + (vote === 'absent' ? '#6C757D' : colors[vote]) + '">' + n + ' ' + forms[vote][n > 1 ? 1 : 0] + '</span>');
        }
      });
      center.innerHTML = '<div class="hemicycle-title">' + dot(groupe.c) + escapeHtml(abbrev) + '</div>' +
        '<div class="hemicycle-details hemicycle-long">' + escapeHtml(groupe.l) + ' · ' + groupe.n + ' députés</div>' +
        '<div class="hemicycle-details font-weight-bold">' + details.join(' · ') + '</div>';
    }

    function showTooltip(seat) {
      var abbrev = seat.getAttribute('data-g');
      var vote = seat.getAttribute('data-v');
      var depute = deputes[seat.hemicycleIndex];
      var groupe = groupes[abbrev];
      tooltip.innerHTML = '<div class="hemicycle-card">' +
        '<img src="' + urls.photo + 'depute_' + encodeURIComponent(depute.i) + '_webp.webp" alt="" onerror="this.onerror=null;this.src=\'' + urls.placeholder + '\'">' +
        '<div><div class="hemicycle-name">' + escapeHtml(depute.n) + '</div>' +
        (depute.d ? '<div class="hemicycle-place">' + escapeHtml(depute.d) + (depute.c ? '<br>' + (depute.c === 1 ? '1re' : depute.c + 'e') + ' circonscription' : '') + '</div>' : '') +
        (groupe ? '<div>' + dot(groupe.c) + escapeHtml(abbrev) + '</div>' : '') +
        '<span class="hemicycle-badge" style="background-color:' + (vote === 'absent' ? '#9E9E9E' : colors[vote]) + '">' + escapeHtml(labels[vote].toUpperCase()) + '</span>' +
        '</div></div>';
      tooltip.hidden = false;
      if (selected) {
        selected.classList.remove('selected');
      }
      selected = seat;
      seat.classList.add('selected');

      // Floating card above the seat (on mobile, CSS places it below the hemicycle)
      if (window.getComputedStyle(tooltip).position === 'absolute') {
        var seatBox = seat.getBoundingClientRect();
        var wrapperBox = wrapper.getBoundingClientRect();
        var x = seatBox.left - wrapperBox.left + seatBox.width / 2 - tooltip.offsetWidth / 2;
        var y = seatBox.top - wrapperBox.top - tooltip.offsetHeight - 8;
        if (y < 0) {
          y = seatBox.bottom - wrapperBox.top + 8;
        }
        tooltip.style.left = Math.max(0, Math.min(x, wrapper.offsetWidth - tooltip.offsetWidth)) + 'px';
        tooltip.style.top = y + 'px';
      }
    }

    function hideTooltip() {
      tooltip.hidden = true;
      if (selected) {
        selected.classList.remove('selected');
        selected = null;
      }
    }

    function reset() {
      hideTooltip();
      focusGroup(null);
    }

    function openDepute(seat, newTab) {
      var url = urls.depute + deputes[seat.hemicycleIndex].u;
      if (newTab) {
        window.open(url, '_blank');
      } else {
        window.location.href = url;
      }
    }

    // Mouse: hover; touch and pen: first tap shows the card, second tap opens the MP page
    root.addEventListener('pointerdown', function(event) { pointerType = event.pointerType; });

    svg.addEventListener('pointerover', function(event) {
      if (event.pointerType !== 'mouse') {
        return;
      }
      var seat = event.target.closest('circle[data-g]');
      if (seat) {
        focusGroup(seat.getAttribute('data-g'));
        showTooltip(seat);
      } else {
        reset();
      }
    });
    svg.addEventListener('pointerleave', function(event) {
      if (event.pointerType === 'mouse') {
        reset();
      }
    });
    svg.addEventListener('click', function(event) {
      var seat = event.target.closest('circle[data-g]');
      if (!seat) {
        if (pointerType !== 'mouse') {
          reset();
        }
        return;
      }
      if (pointerType !== 'mouse' && seat !== selected) {
        focusGroup(seat.getAttribute('data-g'));
        showTooltip(seat);
        return;
      }
      openDepute(seat, event.ctrlKey || event.metaKey);
    });
    svg.addEventListener('auxclick', function(event) {
      var seat = event.target.closest('circle[data-g]');
      if (seat && event.button === 1) {
        openDepute(seat, true);
      }
    });
    document.addEventListener('click', function(event) {
      if (pointerType !== 'mouse' && !root.contains(event.target)) {
        reset();
      }
    });

    chips.forEach(function(chip) {
      var abbrev = chip.getAttribute('data-g');
      var pressed = false;
      chip.addEventListener('pointerdown', function() { pressed = true; });
      chip.addEventListener('pointerenter', function(event) {
        if (event.pointerType === 'mouse') {
          hideTooltip();
          focusGroup(abbrev);
        }
      });
      chip.addEventListener('pointerleave', function(event) {
        if (event.pointerType === 'mouse') {
          reset();
        }
      });
      // Keyboard only: focus triggered by a tap or a click is handled by the click
      chip.addEventListener('focus', function() {
        if (!pressed) {
          hideTooltip();
          focusGroup(abbrev);
        }
      });
      chip.addEventListener('blur', function() {
        pressed = false;
        if (focused === abbrev) {
          focusGroup(null);
        }
      });
      chip.addEventListener('click', function(event) {
        pressed = false;
        // Touch: a tap selects the group, a second tap deselects it
        if (event.detail > 0 && pointerType !== 'mouse') {
          hideTooltip();
          if (focused === abbrev) {
            chip.blur();
            focusGroup(null);
          } else {
            focusGroup(abbrev);
          }
        }
      });
    });

    // Switch between vote and group colors, with a left-to-right sweep
    Array.prototype.slice.call(root.querySelectorAll('[data-mode]')).forEach(function(button) {
      button.addEventListener('click', function() {
        var mode = button.getAttribute('data-mode');
        Array.prototype.slice.call(root.querySelectorAll('[data-mode]')).forEach(function(other) {
          other.classList.toggle('active', other === button);
          other.setAttribute('aria-pressed', other === button ? 'true' : 'false');
        });
        root.querySelector('[data-legend="vote"]').hidden = mode !== 'vote';
        root.querySelector('[data-legend="groupe"]').hidden = mode !== 'groupe';
        seats.forEach(function(seat, i) {
          var groupe = groupes[seat.getAttribute('data-g')];
          var vote = seat.getAttribute('data-v');
          var byGroup = mode === 'groupe' && groupe;
          seat.style.transitionDelay = (i * 0.8) + 'ms, 0ms, ' + (i * 0.8) + 'ms';
          seat.style.fill = byGroup ? groupe.c : colors[vote];
          // In group mode, MPs who did not vote stay pale
          seat.style.fillOpacity = byGroup && (vote === 'absent' || vote === 'non-votant') ? 0.2 : 1;
        });
      });
    });
  })();
</script>
