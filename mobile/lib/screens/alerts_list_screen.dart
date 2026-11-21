import 'package:flutter/material.dart';

import '../services/api_service.dart';
import 'alert_detail_screen.dart';

const List<String> _imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

class AlertsListScreen extends StatefulWidget {
  const AlertsListScreen({super.key});

  @override
  State<AlertsListScreen> createState() => AlertsListScreenState();
}

class AlertsListScreenState extends State<AlertsListScreen> {
  late Future<List<Map<String, dynamic>>> _future;

  @override
  void initState() {
    super.initState();
    _future = ApiService.fetchAlertesRecentes();
  }

  Future<void> refresh() async {
    final future = ApiService.fetchAlertesRecentes();
    setState(() => _future = future);
    await future;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Alertes récentes')),
      body: RefreshIndicator(
        onRefresh: refresh,
        child: FutureBuilder<List<Map<String, dynamic>>>(
          future: _future,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snapshot.hasError) {
              return ListView(
                children: [
                  Padding(
                    padding: const EdgeInsets.all(32),
                    child: Text(
                      'Impossible de charger les alertes.\n${snapshot.error}',
                      textAlign: TextAlign.center,
                    ),
                  ),
                ],
              );
            }
            final alertes = snapshot.data ?? [];
            if (alertes.isEmpty) {
              return ListView(
                children: const [
                  Padding(
                    padding: EdgeInsets.all(32),
                    child: Text('Aucune alerte signalée pour le moment.', textAlign: TextAlign.center),
                  ),
                ],
              );
            }
            return ListView.builder(
              padding: const EdgeInsets.all(12),
              itemCount: alertes.length,
              itemBuilder: (context, index) => _AlertCard(alerte: alertes[index]),
            );
          },
        ),
      ),
    );
  }
}

class _AlertCard extends StatelessWidget {
  final Map<String, dynamic> alerte;

  const _AlertCard({required this.alerte});

  @override
  Widget build(BuildContext context) {
    final fichier = alerte['fichier'] as String?;
    final isImage = fichier != null &&
        _imageExtensions.contains(fichier.split('.').last.toLowerCase());
    final statut = alerte['new_statut'] as String?;

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => AlertDetailScreen(alerte: alerte)),
        ),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(6),
                child: fichier == null
                    ? _placeholder()
                    : isImage
                        ? Image.network(
                            ApiService.fileUrl(fichier),
                            width: 64,
                            height: 64,
                            fit: BoxFit.cover,
                            errorBuilder: (context, error, stackTrace) => _placeholder(),
                          )
                        : _placeholder(icon: Icons.videocam),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '${alerte['commune'] ?? '—'} • ${alerte['quartier'] ?? '—'}',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 4),
                    Wrap(
                      spacing: 6,
                      children: [
                        _badge(alerte['risques']?.toString() ?? '—', Colors.blueGrey),
                        _statusBadge(statut),
                      ],
                    ),
                    const SizedBox(height: 6),
                    if ((alerte['description'] as String?)?.isNotEmpty == true)
                      Text(
                        alerte['description'],
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    const SizedBox(height: 4),
                    Text(
                      _formatDate(alerte['date'] as String?),
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _placeholder({IconData icon = Icons.image_not_supported}) {
    return Container(
      width: 64,
      height: 64,
      color: Colors.grey.shade300,
      child: Icon(icon, color: Colors.grey.shade700),
    );
  }

  Widget _badge(String label, Color color) {
    return Chip(
      label: Text(label, style: const TextStyle(fontSize: 11, color: Colors.white)),
      backgroundColor: color,
      padding: EdgeInsets.zero,
      visualDensity: VisualDensity.compact,
    );
  }

  Widget _statusBadge(String? statut) {
    if (statut == 'traite') return _badge('Traité', Colors.green);
    return _badge('Non traité', Colors.orange);
  }

  String _formatDate(String? raw) {
    if (raw == null || raw.isEmpty) return '—';
    final date = DateTime.tryParse(raw);
    if (date == null) return raw;
    return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';
  }
}
