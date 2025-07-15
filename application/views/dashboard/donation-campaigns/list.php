<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row my-4">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary font-weight-bold"><?= $title ?></h1>

                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Message</th>
                                    <th>Date de début</th>
                                    <th>Date de fin</th>
                                    <th>Auteur</th>
                                    <th>Position</th>
                                    <th>Page</th>
                                    <th>Actions</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($campaigns as $campaign): ?>
                                    <?php $switchId = 'toggleCampaignActive_' . $campaign['id']; ?>
                                    <tr>
                                        <td><?= $campaign['id'] ?></td>
                                        <td><?= $campaign['text'] ?></td>
                                        <td><?= $campaign['start_date'] ?></td>
                                        <td><?= $campaign['end_date'] ?></td>
                                        <td><?= $campaign['author_name'] ?></td>
                                        <td><?= $positions_labels[$campaign['position']] ?? '' ?></td>
                                        <td><?= $campaign['page'] ?></td>
                                        <td>
                                            <a href="<?= base_url('admin/campagnes/edit/' . $campaign['id']) ?>" class="btn btn-sm btn-warning">Modifier</a>
                                            <a href="<?= base_url('admin/campagnes/delete/' . $campaign['id']) ?>" class="btn btn-sm btn-danger">Supprimer</a>
                                            <?= form_open('/admin/campagnes/toggle', ['class' => 'toggle-form']) ?>
                                            <input type="hidden" name="id" value="<?= $campaign['id'] ?>">
                                            <input type="hidden" name="is_active" value="0">
                                            <div class="custom-control custom-switch mt-2">
                                                <input
                                                    type="checkbox"
                                                    class="custom-control-input"
                                                    id="<?= $switchId ?>"
                                                    name="is_active"
                                                    value="1"
                                                    onchange="this.form.submit()"
                                                    <?= $campaign['is_active'] ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="<?= $switchId ?>">Activer la campagne</label>
                                            </div>
                                            <?= form_close() ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>