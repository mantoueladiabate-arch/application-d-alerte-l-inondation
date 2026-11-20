import 'package:flutter/material.dart';

import '../services/api_service.dart';

const List<String> _imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

class AlertDetailScreen extends StatelessWidget {
  final Map<String, dynamic> alerte;

  const AlertDetailScreen({super.key, required this.alerte});

  @override
  Widget build(BuildContext context) {
    final fichier = alerte['fichier'] as String?;
    final isImage = fichier != null &&
        _imageExtensions.contains(fichier.split('.').last.toLowerCase());
    final statut = alerte['new_statut'] as String?;
    final recommandation = alerte['recommandation'] as String?;

    return Scaffold(
      appBar: AppBar(title: Text('Alerte #${alerte['id']}')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (fichier != null)
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: isImage
                    ? Image.network(
                        ApiService.fileUrl(fichier),
                        width: double.infinity,
                        height: 220,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) => _filePlaceholder(fichier),
                      )
                    : _filePlaceholder(fichier),
              ),
            const SizedBox(height: 16),
            Wrap(
              spacing: 8,
              children: [
                Chip(label: Text(alerte['risques']?.toString() ?? '—')),
                Chip(
                  label: Text(statut == 'traite' ? 'Traité' : 'Non traité'),
                  backgroundColor: statut == 'traite' ? Colors.green.shade100 : Colors.orange.shade100,
                ),
              ],
            ),
            const SizedBox(height: 16),
            _row('Commune', alerte['commune']?.toString()),
            _row('Quartier', alerte['quartier']?.toString()),
            _row('Date', alerte['date']?.toString()),
            _row(
              'Position',
              alerte['latitude'] != null && alerte['longitude'] != null
                  ? '${alerte['latitude']}, ${alerte['longitude']}'
                  : null,
            ),
            const SizedBox(height: 12),
            Text('Description', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 4),
            Text((alerte['description'] as String?)?.isNotEmpty == true
                ? alerte['description']
                : 'Aucune description.'),
            if (recommandation != null && recommandation.isNotEmpty) ...[
              const SizedBox(height: 16),
              Text('Recommandation', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 4),
              Text(recommandation),
            ],
          ],
        ),
      ),
    );
  }

  Widget _filePlaceholder(String fichier) {
    return Container(
      width: double.infinity,
      height: 120,
      color: Colors.grey.shade300,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.videocam, size: 40, color: Colors.grey),
          const SizedBox(height: 4),
          Text(fichier, style: const TextStyle(color: Colors.grey)),
        ],
      ),
    );
  }

  Widget _row(String label, String? value) {
    if (value == null || value.isEmpty) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        children: [
          SizedBox(
            width: 100,
            child: Text(label, style: const TextStyle(fontWeight: FontWeight.bold)),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
