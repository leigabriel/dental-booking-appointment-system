<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();

// Data variables passed from Management::service_edit
$service_data = $service ?? [];
$errors = $errors ?? [];
$post_data = $post_data ?? [];

// Repopulate logic: use failed form data (post_data) if present, otherwise use existing service data
$id = $service_data['id'] ?? ($post_data['id'] ?? '');
$name = $post_data['name'] ?? $service_data['name'] ?? '';
$price = $post_data['price'] ?? $service_data['price'] ?? '';
$duration_mins = $post_data['duration_mins'] ?? $service_data['duration_mins'] ?? '';

$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');

function display_errors($errors)
{
  if (!empty($errors)) {
    echo '<div class="p-3 mb-4 rounded-lg bg-red-100 text-red-700 border border-red-300">';
    echo '<ul>';
    foreach ($errors as $error) {
      echo '<li>' . html_escape($error) . '</li>';
    }
    echo '</ul></div>';
  }
}

// Check if we have service data to edit
if (empty($service_data) && empty($post_data)) {
  // If somehow we land here without data, redirect to the list
  redirect('management/services');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Service: <?= html_escape($name) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root {
      --primary-color: #0EA5E9;
      --primary-hover: #0284C7;
    }

    body {
      font-family: 'JetBrains Mono', monospace;
    }
  </style>
</head>

<body class="bg-gray-50 p-6 sm:p-10 min-h-screen">
  <div class="max-w-xl mx-auto">
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-lg border border-gray-200 mb-8">
      <h1 class="text-3xl font-extrabold text-[--primary-color]">
        Edit Service: ID #<?= html_escape($id) ?>
      </h1>
      <a href="<?= site_url('management/services') ?>" class="text-sm text-gray-600 hover:text-[--primary-color] font-medium">
        ← Back to Service List
      </a>
    </div>

    <?php if ($flash_message): ?>
      <div class="p-4 mb-4 rounded-lg <?= $LAVA->session->flashdata('success_message') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= html_escape($flash_message) ?>
      </div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Updating <?= html_escape($service_data['name'] ?? 'Service') ?></h2>

      <?php // Display validation errors
      if (!empty($errors)): ?>
        <div class="p-3 mb-4 rounded-lg bg-red-100 text-red-700 border border-red-300">
          Validation failed:
          <?php display_errors($errors); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= site_url('management/service_add_update/' . html_escape($id)) ?>" class="space-y-4">
        <?= csrf_field() ?>

        <input type="hidden" name="id" value="<?= html_escape($id) ?>">

        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Service Name</label>
          <input type="text" id="name" name="name"
            value="<?= html_escape($name) ?>" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="price" class="block text-sm font-medium text-gray-700">Price (USD)</label>
            <input type="number" step="0.01" id="price" name="price"
              value="<?= html_escape($price) ?>" required
              class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
          </div>
          <div>
            <label for="duration_mins" class="block text-sm font-medium text-gray-700">Duration (Minutes)</label>
            <input type="number" id="duration_mins" name="duration_mins"
              value="<?= html_escape($duration_mins) ?>" required
              class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
          </div>
        </div>

        <button type="submit" class="w-full bg-green-500 text-white py-2.5 rounded-lg font-semibold hover:bg-green-600 transition">
          Save Changes
        </button>
      </form>
    </div>
  </div>
</body>

</html>